<?php

namespace App\Services\Import;

use App\Models\Account;
use App\Models\Category;
use App\Models\ExpenseFor;
use App\Models\PaymentMethod;
use App\Models\PaymentPlatform;
use App\Models\Vendor;
use RuntimeException;
use ZipArchive;

class ExpenseImportSampleBuilder
{
    /** @var array<int, string> */
    private array $headers = [
        'expense_date', 'description', 'amount', 'cashback', 'category', 'expense_for',
        'payment_method', 'payment_platform', 'account', 'vendor', 'nature',
        'transaction_kind', 'frequency', 'notes',
    ];

    public function build(int $userId): string
    {
        $path = storage_path('app/imports/tmp/expense-import-sample-'.uniqid('', true).'.xlsx');
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create import temp directory.');
        }

        $masters = $this->masters($userId);
        $sampleRows = $this->sampleRows($masters);
        $masterColumns = $this->masterColumns($masters);

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create XLSX sample file.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels());
        $zip->addFromString('xl/styles.xml', $this->styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->expenseSheet($sampleRows, $masterColumns));
        $zip->addFromString('xl/worksheets/sheet2.xml', $this->mastersSheet($masterColumns));
        $zip->close();

        return $path;
    }

    /** @return array<string, array<int, string>> */
    private function masters(int $userId): array
    {
        return [
            'categories' => Category::active()
                ->with('parent')
                ->orderByRaw('parent_id IS NOT NULL')
                ->orderBy('parent_id')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->pluck('full_name')
                ->filter()
                ->unique()
                ->values()
                ->all(),
            'expense_fors' => ExpenseFor::orderBy('name')->pluck('name')->filter()->unique()->values()->all(),
            'payment_methods' => PaymentMethod::orderBy('name')->pluck('name')->filter()->unique()->values()->all(),
            'payment_platforms' => PaymentPlatform::where('user_id', $userId)->where('is_active', true)->orderBy('name')->pluck('name')->filter()->unique()->values()->all(),
            'accounts' => Account::where('user_id', $userId)->where('is_active', true)->orderBy('name')->pluck('name')->filter()->unique()->values()->all(),
            'vendors' => Vendor::where('user_id', $userId)->where('is_active', true)->orderBy('name')->pluck('name')->filter()->unique()->values()->all(),
            'natures' => ['need', 'want', 'investment'],
            'transaction_kinds' => ['expense', 'investment', 'transfer', 'debt'],
            'frequencies' => ['daily', 'occasionally', 'weekly', 'monthly', 'quarterly', 'yearly'],
        ];
    }

    /** @param array<string, array<int, string>> $masters */
    private function sampleRows(array $masters): array
    {
        return [
            [
                '2026-07-04', 'Milk', 72, 0,
                $this->firstContaining($masters['categories'], 'Milk') ?? $masters['categories'][0] ?? '',
                $masters['expense_fors'][0] ?? '',
                $this->firstContaining($masters['payment_methods'], 'UPI') ?? $masters['payment_methods'][0] ?? '',
                $masters['payment_platforms'][0] ?? '',
                $masters['accounts'][0] ?? '',
                $masters['vendors'][0] ?? '',
                'need', 'expense', 'daily', 'Daily milk',
            ],
            [
                '2026-07-05', 'Mutual Fund SIP', 5000, 0,
                $this->firstContaining($masters['categories'], 'Mutual Fund') ?? $masters['categories'][0] ?? '',
                $masters['expense_fors'][0] ?? '',
                $this->firstContaining($masters['payment_methods'], 'Bank') ?? $masters['payment_methods'][0] ?? '',
                '',
                $masters['accounts'][0] ?? '',
                '',
                'investment', 'investment', 'monthly', 'Monthly SIP',
            ],
        ];
    }

    private function firstContaining(array $values, string $needle): ?string
    {
        foreach ($values as $value) {
            if (stripos($value, $needle) !== false) return $value;
        }

        return null;
    }

    /** @param array<string, array<int, string>> $masters */
    private function masterColumns(array $masters): array
    {
        return [
            ['title' => 'Categories', 'values' => $masters['categories'], 'expense_column' => 'E'],
            ['title' => 'Expense For', 'values' => $masters['expense_fors'], 'expense_column' => 'F'],
            ['title' => 'Payment Method', 'values' => $masters['payment_methods'], 'expense_column' => 'G'],
            ['title' => 'Payment Platform', 'values' => $masters['payment_platforms'], 'expense_column' => 'H'],
            ['title' => 'Account', 'values' => $masters['accounts'], 'expense_column' => 'I'],
            ['title' => 'Vendor', 'values' => $masters['vendors'], 'expense_column' => 'J'],
            ['title' => 'Nature', 'values' => $masters['natures'], 'expense_column' => 'K'],
            ['title' => 'Transaction Kind', 'values' => $masters['transaction_kinds'], 'expense_column' => 'L'],
            ['title' => 'Frequency', 'values' => $masters['frequencies'], 'expense_column' => 'M'],
        ];
    }

    private function expenseSheet(array $sampleRows, array $masterColumns): string
    {
        $rows = [];
        $rows[] = $this->rowXml(1, $this->headers, 1);
        $rowNumber = 2;
        foreach ($sampleRows as $row) {
            $rows[] = $this->rowXml($rowNumber, $row);
            $rowNumber++;
        }

        $validations = [];
        foreach ($masterColumns as $index => $master) {
            if (empty($master['values'])) continue;
            $masterColumn = $this->columnName($index + 1);
            $lastRow = count($master['values']) + 1;
            $expenseColumn = $master['expense_column'];
            $validations[] = '<dataValidation type="list" allowBlank="1" showErrorMessage="1" sqref="'.$expenseColumn.'2:'.$expenseColumn.'501"><formula1>\'Masters\'!$'.$masterColumn.'$2:$'.$masterColumn.'$'.$lastRow.'</formula1></dataValidation>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="15"/>'
            .'<cols>'.$this->colsXml([12, 28, 12, 12, 32, 18, 20, 22, 22, 24, 16, 18, 16, 34]).'</cols>'
            .'<sheetData>'.implode('', $rows).'</sheetData>'
            .($validations ? '<dataValidations count="'.count($validations).'">'.implode('', $validations).'</dataValidations>' : '')
            .'</worksheet>';
    }

    private function mastersSheet(array $masterColumns): string
    {
        $maxRows = 1;
        foreach ($masterColumns as $master) $maxRows = max($maxRows, count($master['values']) + 1);

        $rows = [];
        $rows[] = $this->rowXml(1, array_column($masterColumns, 'title'), 1);
        for ($rowNumber = 2; $rowNumber <= $maxRows; $rowNumber++) {
            $row = [];
            foreach ($masterColumns as $master) {
                $row[] = $master['values'][$rowNumber - 2] ?? '';
            }
            $rows[] = $this->rowXml($rowNumber, $row);
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="15"/>'
            .'<cols>'.$this->colsXml(array_fill(0, count($masterColumns), 28)).'</cols>'
            .'<sheetData>'.implode('', $rows).'</sheetData>'
            .'</worksheet>';
    }

    private function rowXml(int $rowNumber, array $values, int $style = 0): string
    {
        $cells = [];
        foreach (array_values($values) as $index => $value) {
            $cell = $this->columnName($index + 1).$rowNumber;
            $styleAttr = $style > 0 ? ' s="'.$style.'"' : '';
            if (is_numeric($value) && $value !== '') {
                $cells[] = '<c r="'.$cell.'"'.$styleAttr.'><v>'.$value.'</v></c>';
            } else {
                $cells[] = '<c r="'.$cell.'" t="inlineStr"'.$styleAttr.'><is><t>'.$this->xml((string) $value).'</t></is></c>';
            }
        }

        return '<row r="'.$rowNumber.'">'.implode('', $cells).'</row>';
    }

    private function colsXml(array $widths): string
    {
        $xml = '';
        foreach ($widths as $index => $width) {
            $column = $index + 1;
            $xml .= '<col min="'.$column.'" max="'.$column.'" width="'.$width.'" customWidth="1"/>';
        }
        return $xml;
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }
        return $name;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets>'
            .'<sheet name="Expenses" sheetId="1" r:id="rId1"/>'
            .'<sheet name="Masters" sheetId="2" r:id="rId2"/>'
            .'</sheets>'
            .'</workbook>';
    }

    private function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>'
            .'<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }
}

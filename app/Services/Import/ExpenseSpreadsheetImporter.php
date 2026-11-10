<?php

namespace App\Services\Import;

use App\DTOs\ExpenseDTO;
use App\Models\Account;
use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseFor;
use App\Models\PaymentMethod;
use App\Models\PaymentPlatform;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use ZipArchive;

class ExpenseSpreadsheetImporter
{
    private const REQUIRED_HEADERS = ['expense_date', 'description', 'amount', 'category', 'expense_for', 'payment_method', 'account'];
    private const KINDS = ['expense', 'investment', 'transfer', 'debt'];
    private const NATURES = ['need', 'want', 'investment'];
    private const FREQUENCIES = ['daily', 'occasionally', 'weekly', 'monthly', 'quarterly', 'yearly'];

    public function analyse(string $file, int $userId, bool $createVendors = false, bool $allowDuplicates = false): array
    {
        $sheet = $this->read($file);
        if (count($sheet) < 2) throw new RuntimeException('Import file has no data rows.');

        $headers = array_map(fn ($value) => $this->header($value), array_shift($sheet));
        $missing = array_diff(self::REQUIRED_HEADERS, $headers);
        if ($missing !== []) throw new RuntimeException('Missing required columns: '.implode(', ', $missing));

        $categories = Category::with('parent')->get();
        $categoryMap = [];
        $simpleNames = [];
        foreach ($categories as $category) {
            $categoryMap[$this->key($category->full_name)] = $category->id;
            $simpleNames[$this->key($category->name)][] = $category->id;
        }
        foreach ($simpleNames as $name => $ids) if (count($ids) === 1) $categoryMap[$name] = $ids[0];

        $expenseForMap = ExpenseFor::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [$this->key($name) => $id]);
        $methodMap = PaymentMethod::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [$this->key($name) => $id]);
        $accounts = Account::with(['paymentMethods:id', 'paymentPlatforms:id'])->where('user_id', $userId)->get();
        $accountMap = $accounts->keyBy(fn ($account) => $this->key($account->name));
        $platforms = PaymentPlatform::where('user_id', $userId)->where('is_active', true)->get();
        $platformMap = $platforms->keyBy(fn ($platform) => $this->key($platform->name));
        $vendorMap = Vendor::where('user_id', $userId)->pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [$this->key($name) => $id]);

        $existing = Expense::where('created_by', $userId)->get(['expense_date', 'description', 'amount', 'category_id', 'account_id'])
            ->mapWithKeys(fn ($expense) => [$this->signature([
                'expense_date' => $expense->expense_date->format('Y-m-d'), 'description' => $expense->description,
                'amount' => (float) $expense->amount, 'category_id' => $expense->category_id, 'account_id' => $expense->account_id,
            ]) => true]);

        $valid = []; $errors = []; $duplicates = []; $seen = [];
        foreach ($sheet as $index => $values) {
            if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) continue;
            $rowNumber = $index + 2;
            $row = array_combine($headers, array_pad(array_slice($values, 0, count($headers)), count($headers), ''));
            $rowErrors = [];

            try { $date = $this->date($row['expense_date'] ?? ''); } catch (RuntimeException $e) { $date = null; $rowErrors[] = $e->getMessage(); }
            $description = trim((string) ($row['description'] ?? ''));
            if ($description === '') $rowErrors[] = 'description is required';
            if (mb_strlen($description) > 500) $rowErrors[] = 'description exceeds 500 characters';
            $amount = $this->number($row['amount'] ?? null);
            if ($amount === null || $amount < 1 || $amount > 1000000) $rowErrors[] = 'amount must be between 1 and 1000000';
            $cashback = $this->number($row['cashback'] ?? 0) ?? 0;
            if ($cashback < 0) $rowErrors[] = 'cashback cannot be negative';

            $categoryId = $categoryMap[$this->key($row['category'] ?? '')] ?? null;
            if (!$categoryId) $rowErrors[] = 'unknown or ambiguous category: '.($row['category'] ?? '');
            $expenseForId = $expenseForMap->get($this->key($row['expense_for'] ?? ''));
            if (!$expenseForId) $rowErrors[] = 'unknown expense_for: '.($row['expense_for'] ?? '');
            $methodId = $methodMap->get($this->key($row['payment_method'] ?? ''));
            if (!$methodId) $rowErrors[] = 'unknown payment_method: '.($row['payment_method'] ?? '');
            $account = $accountMap->get($this->key($row['account'] ?? ''));
            if (!$account) $rowErrors[] = 'unknown account for this user: '.($row['account'] ?? '');

            $platformName = trim((string) ($row['payment_platform'] ?? ''));
            $platform = $platformName !== '' ? $platformMap->get($this->key($platformName)) : null;
            if ($platformName !== '' && !$platform) $rowErrors[] = 'unknown payment_platform: '.$platformName;
            if ($account && $methodId && !$account->paymentMethods->contains($methodId)) $rowErrors[] = 'account does not support selected payment_method';
            if ($platform && $methodId && (int) $platform->payment_method_id !== (int) $methodId) $rowErrors[] = 'platform does not belong to selected payment_method';
            if ($platform && $account && !$account->paymentPlatforms->contains($platform->id)) $rowErrors[] = 'platform is not linked to selected account';
            if (!$platform && $account && $methodId && $platforms->where('payment_method_id', $methodId)->whereIn('id', $account->paymentPlatforms->pluck('id'))->isNotEmpty()) $rowErrors[] = 'payment_platform is required for this method/account';

            $kind = strtolower(trim((string) ($row['transaction_kind'] ?? 'expense'))) ?: 'expense';
            if (!in_array($kind, self::KINDS, true)) $rowErrors[] = 'transaction_kind must be '.implode('/', self::KINDS);
            $nature = strtolower(trim((string) ($row['nature'] ?? 'need'))) ?: 'need';
            if (!in_array($nature, self::NATURES, true)) $rowErrors[] = 'nature must be '.implode('/', self::NATURES);
            $frequency = strtolower(trim((string) ($row['frequency'] ?? 'occasionally'))) ?: 'occasionally';
            if (!in_array($frequency, self::FREQUENCIES, true)) $rowErrors[] = 'invalid frequency';

            $vendorName = trim((string) ($row['vendor'] ?? ''));
            $vendorId = $vendorName !== '' ? $vendorMap->get($this->key($vendorName)) : null;
            if ($vendorName !== '' && !$vendorId && !$createVendors) $rowErrors[] = 'unknown vendor (use --create-vendors): '.$vendorName;

            if ($rowErrors !== []) { $errors[] = ['row' => $rowNumber, 'errors' => implode('; ', $rowErrors)]; continue; }

            $data = [
                'category_id' => (int) $categoryId, 'expense_for_id' => (int) $expenseForId,
                'payment_method_id' => (int) $methodId, 'account_id' => (int) $account->id,
                'payment_platform_id' => $platform?->id, 'vendor_id' => $vendorId,
                'vendor_name' => $vendorId ? null : ($vendorName ?: null), 'expense_date' => $date,
                'description' => $description, 'amount' => $amount, 'cashback' => $cashback,
                'notes' => trim((string) ($row['notes'] ?? '')) ?: null, 'nature' => $nature,
                'transaction_kind' => $kind, 'frequency' => $frequency, 'created_by' => $userId,
            ];
            $signature = $this->signature($data);
            if (!$allowDuplicates && ($existing->has($signature) || isset($seen[$signature]))) {
                $duplicates[] = ['row' => $rowNumber, 'description' => $description, 'amount' => $amount]; continue;
            }
            $seen[$signature] = true;
            $valid[] = ['row' => $rowNumber, 'data' => $data];
        }

        return ['valid' => $valid, 'errors' => $errors, 'duplicates' => $duplicates, 'total_rows' => count($sheet)];
    }

    public function persist(array $valid, int $userId): int
    {
        return DB::transaction(function () use ($valid, $userId): int {
            $created = 0;
            foreach ($valid as $item) {
                $data = $item['data'];
                $vendorName = $data['vendor_name']; unset($data['vendor_name']);
                if (!$data['vendor_id'] && $vendorName) {
                    $data['vendor_id'] = Vendor::firstOrCreate(
                        ['user_id' => $userId, 'name' => $vendorName],
                        ['vendor_type' => 'merchant', 'is_active' => true]
                    )->id;
                }
                Expense::create(ExpenseDTO::fromArray($data)->toArray());
                $created++;
            }
            return $created;
        });
    }

    private function read(string $file): array
    {
        if (!is_file($file) || !is_readable($file)) throw new RuntimeException('File not found or not readable: '.$file);
        return match (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
            'csv' => $this->readCsv($file), 'xlsx' => $this->readXlsx($file),
            default => throw new RuntimeException('Only .csv and .xlsx files are supported.'),
        };
    }

    private function readCsv(string $file): array
    {
        $handle = fopen($file, 'rb'); $first = fgets($handle); rewind($handle);
        $delimiter = substr_count((string) $first, ';') > substr_count((string) $first, ',') ? ';' : ',';
        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($rows === [] && isset($row[0])) $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
            $rows[] = $row;
        }
        fclose($handle); return $rows;
    }

    private function readXlsx(string $file): array
    {
        $zip = new ZipArchive();
        if ($zip->open($file) !== true) throw new RuntimeException('Unable to open XLSX file.');
        $shared = [];
        if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $doc = new \DOMDocument(); $doc->loadXML($xml);
            foreach ((new \DOMXPath($doc))->query('//*[local-name()="si"]') as $item) $shared[] = trim($item->textContent);
        }
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml'); $zip->close();
        if ($sheetXml === false) throw new RuntimeException('XLSX first worksheet is missing.');
        $doc = new \DOMDocument(); $doc->loadXML($sheetXml); $xpath = new \DOMXPath($doc); $rows = [];
        foreach ($xpath->query('//*[local-name()="sheetData"]/*[local-name()="row"]') as $rowNode) {
            $row = [];
            foreach ($xpath->query('./*[local-name()="c"]', $rowNode) as $cell) {
                $reference = $cell->getAttribute('r'); preg_match('/^[A-Z]+/', $reference, $match);
                $column = $this->columnIndex($match[0] ?? 'A');
                $type = $cell->getAttribute('t');
                $valueNode = $xpath->query('./*[local-name()="v"]', $cell)->item(0);
                $inlineNode = $xpath->query('.//*[local-name()="t"]', $cell)->item(0);
                $value = $inlineNode?->textContent ?? $valueNode?->textContent ?? '';
                if ($type === 's') $value = $shared[(int) $value] ?? '';
                $row[$column] = $value;
            }
            if ($row !== []) { ksort($row); $rows[] = array_replace(array_fill(0, max(array_keys($row)) + 1, ''), $row); }
        }
        return $rows;
    }

    private function header(mixed $value): string
    {
        $header = strtolower(trim((string) $value));
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);
        return ['date' => 'expense_date', 'title' => 'description', 'payee' => 'vendor', 'type' => 'transaction_kind'][$header] ?? trim($header, '_');
    }

    private function date(mixed $value): string
    {
        if (is_numeric($value) && (float) $value > 1000) return Carbon::create(1899, 12, 30)->addDays((int) $value)->format('Y-m-d');
        $value = trim((string) $value);
        foreach (['Y-m-d', 'd/m/Y', 'd-M-Y', 'd M Y', 'm/d/Y'] as $format) {
            try { $date = Carbon::createFromFormat('!'.$format, $value); if ($date && $date->format($format) === $value) return $date->format('Y-m-d'); } catch (\Throwable) {}
        }
        throw new RuntimeException('invalid expense_date: '.$value);
    }

    private function number(mixed $value): ?float
    {
        if ($value === null || trim((string) $value) === '') return null;
        $clean = preg_replace('/[^0-9.\-]/', '', str_replace(',', '', (string) $value));
        return is_numeric($clean) ? round((float) $clean, 2) : null;
    }

    private function key(mixed $value): string { return mb_strtolower(trim(preg_replace('/\s+/', ' ', str_replace('>', '/', (string) $value)))); }
    private function signature(array $data): string { return sha1(implode('|', [$data['expense_date'], $this->key($data['description']), number_format((float) $data['amount'], 2, '.', ''), $data['category_id'], $data['account_id']])); }
    private function columnIndex(string $letters): int { $index = 0; foreach (str_split($letters) as $letter) $index = $index * 26 + ord($letter) - 64; return $index - 1; }
}

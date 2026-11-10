<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Import\ExpenseSpreadsheetImporter;
use Illuminate\Console\Command;
use RuntimeException;

class ImportExpensesCommand extends Command
{
    protected $signature = 'expenses:import
                            {file : Absolute or project-relative CSV/XLSX path}
                            {--user= : User ID that will own imported expenses}
                            {--apply : Persist valid rows; without this option only preview}
                            {--create-vendors : Create vendors that do not exist}
                            {--allow-duplicates : Import rows matching an existing expense}';

    protected $description = 'Validate, preview and import expenses from CSV or XLSX';

    public function handle(ExpenseSpreadsheetImporter $importer): int
    {
        $userId = (int) $this->option('user');
        if ($userId < 1 || !User::query()->whereKey($userId)->exists()) {
            $this->error('A valid --user=ID is required.');
            return self::FAILURE;
        }

        $file = (string) $this->argument('file');
        if (!str_starts_with($file, DIRECTORY_SEPARATOR)) $file = base_path($file);

        try {
            $report = $importer->analyse(
                $file,
                $userId,
                (bool) $this->option('create-vendors'),
                (bool) $this->option('allow-duplicates')
            );
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }

        $this->newLine();
        $this->table(['Status', 'Rows'], [
            ['Valid', count($report['valid'])],
            ['Duplicates skipped', count($report['duplicates'])],
            ['Invalid', count($report['errors'])],
        ]);

        if ($report['valid'] !== []) {
            $this->info('Preview (first 20 valid rows)');
            $this->table(
                ['Row', 'Date', 'Description', 'Amount', 'Kind', 'Frequency'],
                collect($report['valid'])->take(20)->map(fn ($item) => [
                    $item['row'], $item['data']['expense_date'], $item['data']['description'],
                    number_format($item['data']['amount'], 2), $item['data']['transaction_kind'], $item['data']['frequency'],
                ])->all()
            );
        }

        if ($report['duplicates'] !== []) {
            $this->warn('Duplicate rows skipped: '.collect($report['duplicates'])->pluck('row')->implode(', '));
        }

        if ($report['errors'] !== []) {
            $this->error('Import validation failed. No rows were inserted.');
            $this->table(['Row', 'Errors'], collect($report['errors'])->take(50)->map(fn ($error) => [$error['row'], $error['errors']])->all());
            return self::FAILURE;
        }

        if (!$this->option('apply')) {
            $this->comment('Dry run only. Re-run with --apply after reviewing this preview.');
            return self::SUCCESS;
        }

        if ($report['valid'] === []) {
            $this->warn('Nothing to import.');
            return self::SUCCESS;
        }

        $created = $importer->persist($report['valid'], $userId);
        $this->info("Imported {$created} expense row(s) successfully.");
        return self::SUCCESS;
    }
}

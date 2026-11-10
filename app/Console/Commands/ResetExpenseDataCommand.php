<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ResetExpenseDataCommand extends Command
{
    protected $signature = 'expenses:fresh-start
                            {--force : Skip confirmation (required in production)}';

    protected $description = 'Permanently empty all expense data and master tables';

    /**
     * Child tables must be listed before the masters they reference.
     *
     * @var list<string>
     */
    private const TABLES = [
        'expenses',
        'budgets',
        'vendors',
        'account_payment_platform',
        'account_payment_method',
        'payment_platforms',
        'accounts',
        'categories',
        'expense_fors',
        'payment_methods',
    ];

    public function handle(): int
    {
        if (app()->environment('production') && !$this->option('force')) {
            $this->error(
                'Refusing to reset production data without the --force option.'
            );

            return self::FAILURE;
        }

        $tables = collect(self::TABLES)
            ->filter(fn (string $table): bool => Schema::hasTable($table))
            ->values();

        $counts = $tables->mapWithKeys(
            fn (string $table): array => [$table => DB::table($table)->count()]
        );

        $this->warn('This permanently deletes expense transactions and masters.');
        $this->table(
            ['Table', 'Rows to delete'],
            $counts->map(
                fn (int $count, string $table): array => [$table, $count]
            )->values()->all()
        );
        $this->line('Users, roles, permissions, menus, and DB backups are not touched.');
        $this->line('Uploaded receipt files are not deleted.');

        if (
            !$this->option('force')
            && !$this->confirm('Continue with expense fresh start?', false)
        ) {
            $this->info('Cancelled. No data was changed.');

            return self::SUCCESS;
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($tables as $table) {
                DB::table($table)->truncate();
                $this->components->task(
                    "Emptying {$table}",
                    static fn (): bool => true
                );
            }
        } catch (Throwable $exception) {
            $this->error('Reset failed: '.$exception->getMessage());

            return self::FAILURE;
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->newLine();
        $this->info('Expense data reset complete. You can now add fresh masters.');

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Services\ExpenseClassificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClassifyExpensesCommand extends Command
{
    protected $signature = 'expenses:classify
                            {--apply : Persist the proposed classifications}
                            {--force : Overwrite classifications already reviewed by a user}
                            {--user= : Limit classification to one user ID}';

    protected $description = 'Preview or apply high-confidence expense classifications';

    public function handle(ExpenseClassificationService $classifier): int
    {
        $query = Expense::query()->with('category.parent.parent');

        if ($this->option('user')) {
            $query->where('created_by', (int) $this->option('user'));
        }

        $summary = [];
        $changed = 0;
        $apply = (bool) $this->option('apply');
        $force = (bool) $this->option('force');

        $operation = function () use (
            $query,
            $classifier,
            &$summary,
            &$changed,
            $apply,
            $force
        ): void {
            $query->orderBy('id')->chunkById(200, function ($expenses) use (
                $classifier,
                &$summary,
                &$changed,
                $apply,
                $force
            ): void {
                foreach ($expenses as $expense) {
                    $classification = $classifier->classify($expense->category);
                    $key = implode(' / ', array_values($classification));
                    $summary[$key] = ($summary[$key] ?? 0) + 1;

                    $changes = array_filter(
                        $classification,
                        function (string $value, string $field) use ($expense, $force): bool {
                            if ($expense->{$field} === $value) {
                                return false;
                            }

                            if ($force) {
                                return true;
                            }

                            return match ($field) {
                                "transaction_kind" => $expense->{$field} === "expense"
                                    && $value !== "expense",
                                "spending_nature" => $expense->{$field} === "unclassified"
                                    && $value !== "unclassified",
                                "recurrence" => $expense->{$field} === "none"
                                    && $value !== "none",
                                default => false,
                            };
                        },
                        ARRAY_FILTER_USE_BOTH
                    );

                    if ($changes === []) {
                        continue;
                    }

                    $changed++;
                    if ($apply) {
                        $expense->update($changes);
                    }
                }
            });
        };

        if ($apply) {
            DB::transaction($operation);
        } else {
            $operation();
        }

        ksort($summary);
        $rows = [];
        foreach ($summary as $classification => $count) {
            [$kind, $nature, $recurrence] = explode(' / ', $classification);
            $rows[] = [$kind, $nature, $recurrence, $count];
        }

        $this->table(
            ['Transaction kind', 'Spending nature', 'Recurrence', 'Rows'],
            $rows
        );
        $this->info(sprintf(
            '%s %d row(s).',
            $apply ? 'Updated' : 'Would update',
            $changed
        ));

        if (!$apply) {
            $this->comment('Dry run only. Re-run with --apply after reviewing this summary.');
        }

        return self::SUCCESS;
    }
}

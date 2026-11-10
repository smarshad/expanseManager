<?php

namespace App\Services;

use App\Models\Expense;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SpendingInsightsService
{
    private const NET_AMOUNT_SQL = 'amount - COALESCE(cashback, 0)';

    /**
     * Build a decision-focused summary without counting transfers as spending.
     *
     * @return array<string, mixed>
     */
    public function generate(
        int $userId,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): array {
        $base = $this->baseQuery($userId, $startDate, $endDate);

        $byKind = (clone $base)
            ->selectRaw('transaction_kind, ROUND(SUM('.self::NET_AMOUNT_SQL.'), 2) AS total')
            ->groupBy('transaction_kind')
            ->pluck('total', 'transaction_kind');

        $byNature = (clone $base)
            ->where('transaction_kind', 'expense')
            ->selectRaw('spending_nature, ROUND(SUM('.self::NET_AMOUNT_SQL.'), 2) AS total')
            ->groupBy('spending_nature')
            ->pluck('total', 'spending_nature');

        $daySpan = $startDate->diffInDays($endDate);
        $previousEnd = $startDate->copy()->subDay();
        $previousStart = $previousEnd->copy()->subDays($daySpan);
        $currentExpenses = $this->money($byKind, 'expense');
        $previousExpenses = (float) $this->baseQuery($userId, $previousStart, $previousEnd)
            ->where('transaction_kind', 'expense')
            ->sum(DB::raw(self::NET_AMOUNT_SQL));

        $change = $previousExpenses > 0
            ? round((($currentExpenses - $previousExpenses) / $previousExpenses) * 100, 2)
            : null;

        $topCategories = (clone $base)
            ->where('transaction_kind', 'expense')
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->leftJoin('categories as parents', 'categories.parent_id', '=', 'parents.id')
            ->selectRaw(
                'COALESCE(parents.name, categories.name) AS name, '.
                'ROUND(SUM('.self::NET_AMOUNT_SQL.'), 2) AS total'
            )
            ->groupByRaw('COALESCE(parents.name, categories.name)')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => [
                'name' => $row->name,
                'total' => (float) $row->total,
                'percentage' => $currentExpenses > 0
                    ? round(((float) $row->total / $currentExpenses) * 100, 2)
                    : 0,
            ])
            ->all();

        return [
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'previous_start_date' => $previousStart->toDateString(),
                'previous_end_date' => $previousEnd->toDateString(),
            ],
            'cash_flow' => [
                'expenses' => $currentExpenses,
                'investments' => $this->money($byKind, 'investment'),
                'transfers' => $this->money($byKind, 'transfer'),
                'debt_repayments' => $this->money($byKind, 'debt'),
                'total_outflow' => round($byKind->sum(), 2),
            ],
            'spending' => [
                'essential' => $this->money($byNature, 'essential'),
                'discretionary' => $this->money($byNature, 'discretionary'),
                'unclassified' => $this->money($byNature, 'unclassified'),
                'potential_saving' => $this->money($byNature, 'discretionary'),
                'recurring' => round((float) (clone $base)
                    ->where('transaction_kind', 'expense')
                    ->where('recurrence', '!=', 'none')
                    ->sum(DB::raw(self::NET_AMOUNT_SQL)), 2),
            ],
            'comparison' => [
                'previous_expenses' => round($previousExpenses, 2),
                'change_percentage' => $change,
                'direction' => $change === null
                    ? 'no_baseline'
                    : ($change > 0 ? 'up' : ($change < 0 ? 'down' : 'same')),
            ],
            'top_categories' => $topCategories,
        ];
    }

    private function baseQuery(
        int $userId,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): Builder {
        return Expense::query()
            ->where('expenses.created_by', $userId)
            ->whereBetween('expenses.expense_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ]);
    }

    private function money(Collection $values, string $key): float
    {
        return round((float) $values->get($key, 0), 2);
    }
}

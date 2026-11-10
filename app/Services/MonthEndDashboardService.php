<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MonthEndDashboardService
{
    private const NET_SQL = 'expenses.amount - COALESCE(expenses.cashback, 0)';

    private const KIND_SQL = <<<'SQL'
CASE
    WHEN expenses.transaction_kind IN ('investment', 'transfer', 'debt', 'loan') THEN expenses.transaction_kind
    WHEN expenses.nature = 'investment' THEN 'investment'
    WHEN LOWER(COALESCE(parents.name, categories.name)) = 'investment' THEN 'investment'
    WHEN LOWER(COALESCE(parents.name, categories.name)) IN ('transfer', 'finance & transfers') THEN 'transfer'
    WHEN LOWER(categories.name) IN ('cash withdrawal', 'withdrawal', 'credit card bill', 'credit card payment') THEN 'transfer'
    WHEN LOWER(COALESCE(parents.name, categories.name)) = 'lending & borrowing'
         AND LOWER(categories.name) IN ('borrow return', 'loan repaid', 'debt repayment') THEN 'debt'
    ELSE 'expense'
END
SQL;

    public function generate(int $userId, string $month): array
    {
        $start = Carbon::createFromFormat('Y-m-d', "{$month}-01")->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $previousStart = $start->copy()->subMonthNoOverflow()->startOfMonth();
        $previousEnd = $previousStart->copy()->endOfMonth();

        $base = $this->analyticsQuery($userId, $start, $end);
        $previous = $this->analyticsQuery($userId, $previousStart, $previousEnd);

        $byKind = (clone $base)
            ->selectRaw(self::KIND_SQL.' AS kind, ROUND(SUM('.self::NET_SQL.'), 2) AS total')
            ->groupBy('kind')
            ->pluck('total', 'kind');

        $expenseTotal = $this->money($byKind, 'expense');
        $investmentTotal = $this->money($byKind, 'investment');
        $previousExpenseTotal = $this->expenseTotal($previous);
        $changeAmount = round($expenseTotal - $previousExpenseTotal, 2);
        $changePercentage = $previousExpenseTotal > 0
            ? round(($changeAmount / $previousExpenseTotal) * 100, 1)
            : null;

        $byNature = (clone $base)
            ->whereRaw(self::KIND_SQL." = 'expense'")
            ->selectRaw("COALESCE(expenses.nature, 'unclassified') AS nature_key, ROUND(SUM(".self::NET_SQL.')) AS total')
            ->groupByRaw("COALESCE(expenses.nature, 'unclassified')")
            ->pluck('total', 'nature_key');

        $cashback = round((float) (clone $base)
            ->whereRaw(self::KIND_SQL." = 'expense'")
            ->sum('expenses.cashback'), 2);
        $transactionCount = (clone $base)
            ->whereRaw(self::KIND_SQL." = 'expense'")
            ->count('expenses.id');
        $highestExpense = round((float) (clone $base)
            ->whereRaw(self::KIND_SQL." = 'expense'")
            ->max(DB::raw(self::NET_SQL)), 2);
        $daysForAverage = $this->daysForAverage($start, $end);

        $topCategories = $this->groupedTotals(
            $base,
            'COALESCE(parents.name, categories.name)',
            'name',
            10,
            $expenseTotal
        );

        $topVendors = (clone $base)
            ->whereRaw(self::KIND_SQL." = 'expense'")
            ->whereNotNull('expenses.vendor_id')
            ->join('vendors', 'expenses.vendor_id', '=', 'vendors.id')
            ->selectRaw('vendors.name, COUNT(expenses.id) AS transactions, ROUND(SUM('.self::NET_SQL.'), 2) AS total')
            ->groupBy('vendors.id', 'vendors.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row): array => [
                'name' => $row->name,
                'transactions' => (int) $row->transactions,
                'total' => (float) $row->total,
            ])->all();

        $unassignedVendorTotal = round((float) (clone $base)
            ->whereRaw(self::KIND_SQL." = 'expense'")
            ->whereNull('expenses.vendor_id')
            ->sum(DB::raw(self::NET_SQL)), 2);

        $dailyTrend = (clone $base)
            ->whereRaw(self::KIND_SQL." = 'expense'")
            ->selectRaw('DATE(expenses.expense_date) AS date, ROUND(SUM('.self::NET_SQL.'), 2) AS total')
            ->groupByRaw('DATE(expenses.expense_date)')
            ->orderBy('date')
            ->get()
            ->map(fn ($row): array => ['date' => $row->date, 'label' => Carbon::parse($row->date)->format('d M'), 'total' => (float) $row->total])
            ->all();

        $monthlyTrend = collect(range(5, 0))->map(function (int $monthsAgo) use ($userId, $start): array {
            $trendStart = $start->copy()->subMonthsNoOverflow($monthsAgo)->startOfMonth();
            $trendEnd = $trendStart->copy()->endOfMonth();
            return [
                'month' => $trendStart->format('Y-m'),
                'label' => $trendStart->format('M Y'),
                'total' => $this->expenseTotal($this->analyticsQuery($userId, $trendStart, $trendEnd)),
            ];
        })->all();

        $expenseFor = $this->relationTotals($base, 'expense_fors', 'expense_for_id');
        $paymentMethods = $this->relationTotals($base, 'payment_methods', 'payment_method_id');
        $platforms = $this->relationTotals($base, 'payment_platforms', 'payment_platform_id', 'No Platform');
        $accounts = $this->relationTotals($base, 'accounts', 'account_id', 'No Account');

        $recurring = (clone $base)
            ->whereRaw(self::KIND_SQL." = 'expense'")
            ->where('expenses.is_recurring', true)
            ->whereNotNull('expenses.frequency')
            ->where('expenses.frequency', '!=', 'occasionally')
            ->selectRaw(
                'MIN(expenses.description) AS title, expenses.frequency, '.
                'COALESCE(parents.name, categories.name) AS category, COUNT(expenses.id) AS occurrences, '.
                'ROUND(SUM('.self::NET_SQL.'), 2) AS total'
            )
            ->groupByRaw('LOWER(TRIM(expenses.description)), expenses.frequency, COALESCE(parents.name, categories.name)')
            ->orderByDesc('total')
            ->limit(12)
            ->get()
            ->map(fn ($row): array => [
                'title' => $row->title,
                'category' => $row->category,
                'frequency' => $row->frequency,
                'occurrences' => (int) $row->occurrences,
                'total' => (float) $row->total,
            ])->all();

        $recent = $this->expenseList(clone $base, 'expenses.expense_date', 10);
        $highest = $this->expenseList(clone $base, DB::raw(self::NET_SQL), 8);

        $dayRows = (clone $base)
            ->whereRaw(self::KIND_SQL." = 'expense'")
            ->selectRaw('DAYOFWEEK(expenses.expense_date) AS day_number, ROUND(SUM('.self::NET_SQL.'), 2) AS total')
            ->groupByRaw('DAYOFWEEK(expenses.expense_date)')
            ->pluck('total', 'day_number');
        $dayNames = [2 => 'Mon', 3 => 'Tue', 4 => 'Wed', 5 => 'Thu', 6 => 'Fri', 7 => 'Sat', 1 => 'Sun'];
        $heatmap = collect($dayNames)->map(fn (string $label, int $number): array => [
            'label' => $label,
            'total' => round((float) $dayRows->get($number, 0), 2),
        ])->values()->all();
        $weekendTotal = round((float) $dayRows->only([1, 7])->sum(), 2);
        $weekdayTotal = round((float) $dayRows->except([1, 7])->sum(), 2);

        $calendar = (clone $base)
            ->whereRaw(self::KIND_SQL." = 'expense'")
            ->selectRaw('DATE(expenses.expense_date) AS date, COUNT(*) AS transactions, ROUND(SUM('.self::NET_SQL.'), 2) AS total')
            ->groupByRaw('DATE(expenses.expense_date)')
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->date)->day)
            ->map(fn ($row): array => ['transactions' => (int) $row->transactions, 'total' => (float) $row->total])
            ->all();

        $cashbackByPlatform = (clone $base)
            ->whereRaw(self::KIND_SQL." = 'expense'")
            ->where('expenses.cashback', '>', 0)
            ->leftJoin('payment_platforms as cashback_platforms', 'expenses.payment_platform_id', '=', 'cashback_platforms.id')
            ->selectRaw("COALESCE(cashback_platforms.name, 'No Platform') AS name, ROUND(SUM(expenses.cashback), 2) AS total")
            ->groupByRaw("COALESCE(cashback_platforms.name, 'No Platform')")
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row): array => ['name' => $row->name, 'total' => (float) $row->total])
            ->all();

        $opportunities = $this->savingsOpportunities($base, $previous);
        $budgets = $this->budgetAlerts($userId, $start, $base);
        $story = $this->buildStory($expenseTotal, $topCategories, $changePercentage, $cashback, $topVendors, $budgets);
        $healthScore = $this->healthScore(
            $expenseTotal,
            $this->money($byNature, 'want'),
            $changePercentage,
            $budgets,
            $unassignedVendorTotal
        );

        return [
            'month' => $month,
            'period_label' => $start->format('F Y'),
            'summary' => [
                'expenses' => $expenseTotal,
                'previous_expenses' => $previousExpenseTotal,
                'change_amount' => $changeAmount,
                'change_percentage' => $changePercentage,
                'investments' => $investmentTotal,
                'total_expense_including_investment' => round($expenseTotal + $investmentTotal, 2),
                'transfers' => $this->money($byKind, 'transfer'),
                'debt' => round($this->money($byKind, 'debt') + $this->money($byKind, 'loan'), 2),
                'need' => $this->money($byNature, 'need'),
                'want' => $this->money($byNature, 'want'),
                'unclassified' => $this->money($byNature, 'unclassified'),
                'cashback' => $cashback,
                'transactions' => $transactionCount,
                'daily_average' => $daysForAverage > 0 ? round($expenseTotal / $daysForAverage, 2) : 0,
                'highest_expense' => $highestExpense,
            ],
            'expense_breakdown' => [
                ['label' => 'Actual Expenses', 'total' => $expenseTotal],
                ['label' => 'Investments', 'total' => $investmentTotal],
                ['label' => 'Transfers', 'total' => $this->money($byKind, 'transfer')],
                ['label' => 'Debt Repayment', 'total' => round($this->money($byKind, 'debt') + $this->money($byKind, 'loan'), 2)],
            ],
            'need_vs_want' => [
                'need' => $this->money($byNature, 'need'),
                'want' => $this->money($byNature, 'want'),
                'unclassified' => $this->money($byNature, 'unclassified'),
            ],
            'top_categories' => $topCategories,
            'top_vendors' => $topVendors,
            'unassigned_vendor_total' => $unassignedVendorTotal,
            'daily_trend' => $dailyTrend,
            'monthly_trend' => $monthlyTrend,
            'expense_for' => $expenseFor,
            'payment_methods' => $paymentMethods,
            'payment_platforms' => $platforms,
            'accounts' => $accounts,
            'budget_alerts' => $budgets,
            'recurring_expenses' => $recurring,
            'recent_expenses' => $recent,
            'highest_expenses' => $highest,
            'weekday_weekend' => ['weekday' => $weekdayTotal, 'weekend' => $weekendTotal],
            'day_heatmap' => $heatmap,
            'calendar' => $calendar,
            'calendar_days' => $end->day,
            'calendar_start_offset' => $start->dayOfWeekIso - 1,
            'cashback_by_platform' => $cashbackByPlatform,
            'savings_opportunities' => $opportunities,
            'story' => $story,
            'health_score' => $healthScore,
            'quick_add' => $this->quickAddLinks(),
        ];
    }

    public const CARDS = ['expense', 'investment', 'total', 'need', 'want', 'cashback'];

    /**
     * Transactions behind one summary card (same filters as the card totals).
     */
    public function cardDetails(int $userId, string $month, string $card, ?int $limit = 500): array
    {
        $start = Carbon::createFromFormat('Y-m-d', "{$month}-01")->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $query = $this->analyticsQuery($userId, $start, $end);
        match ($card) {
            'expense' => $query->whereRaw(self::KIND_SQL." = 'expense'"),
            'investment' => $query->whereRaw(self::KIND_SQL." = 'investment'"),
            'total' => $query->whereRaw(self::KIND_SQL." IN ('expense', 'investment')"),
            'need' => $query->whereRaw(self::KIND_SQL." = 'expense'")->whereRaw("COALESCE(expenses.nature, 'unclassified') = 'need'"),
            'want' => $query->whereRaw(self::KIND_SQL." = 'expense'")->whereRaw("COALESCE(expenses.nature, 'unclassified') = 'want'"),
            'cashback' => $query->whereRaw(self::KIND_SQL." = 'expense'")->where('expenses.cashback', '>', 0),
        };

        $valueSql = $card === 'cashback' ? 'COALESCE(expenses.cashback, 0)' : self::NET_SQL;

        $byCategory = (clone $query)
            ->selectRaw('COALESCE(parents.name, categories.name) AS name, COUNT(*) AS transactions, ROUND(SUM('.$valueSql.'), 2) AS total')
            ->groupByRaw('COALESCE(parents.name, categories.name)')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r): array => ['name' => $r->name, 'transactions' => (int) $r->transactions, 'total' => (float) $r->total])
            ->all();

        $rows = (clone $query)
            ->leftJoin('vendors', 'expenses.vendor_id', '=', 'vendors.id')
            ->selectRaw(
                'expenses.id, expenses.expense_date, expenses.description, '.$valueSql.' AS total, '.
                'COALESCE(parents.name, categories.name) AS category, categories.name AS subcategory, vendors.name AS vendor'
            )
            ->orderByDesc('expenses.expense_date')
            ->orderByDesc('expenses.id')
            ->when($limit, fn ($q) => $q->limit($limit))
            ->get()
            ->map(fn ($r): array => [
                'id' => (int) $r->id,
                'date' => Carbon::parse($r->expense_date)->format('d/M/Y'),
                'description' => $r->description,
                'category' => $r->category,
                'subcategory' => $r->subcategory,
                'vendor' => $r->vendor,
                'total' => round((float) $r->total, 2),
            ])->all();

        return [
            'card' => $card,
            'count' => (clone $query)->count('expenses.id'),
            'total' => round((float) (clone $query)->sum(DB::raw($valueSql)), 2),
            'by_category' => $byCategory,
            'rows' => $rows,
        ];
    }

    private function analyticsQuery(int $userId, Carbon $start, Carbon $end): Builder
    {
        return Expense::query()
            ->where('expenses.created_by', $userId)
            ->whereBetween('expenses.expense_date', [$start->toDateString(), $end->toDateString()])
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->leftJoin('categories as parents', 'categories.parent_id', '=', 'parents.id');
    }

    private function expenseTotal(Builder $query): float
    {
        return round((float) (clone $query)
            ->whereRaw(self::KIND_SQL." = 'expense'")
            ->sum(DB::raw(self::NET_SQL)), 2);
    }

    private function groupedTotals(Builder $query, string $expression, string $alias, int $limit, float $total): array
    {
        return (clone $query)
            ->whereRaw(self::KIND_SQL." = 'expense'")
            ->selectRaw($expression.' AS '.$alias.', ROUND(SUM('.self::NET_SQL.'), 2) AS total')
            ->groupByRaw($expression)
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($row): array => [
                'name' => $row->{$alias},
                'total' => (float) $row->total,
                'percentage' => $total > 0 ? round(((float) $row->total / $total) * 100, 1) : 0,
            ])->all();
    }

    private function relationTotals(Builder $query, string $table, string $foreignKey, string $fallback = 'Unassigned'): array
    {
        return (clone $query)
            ->whereRaw(self::KIND_SQL." = 'expense'")
            ->leftJoin($table.' as relation_table', 'expenses.'.$foreignKey, '=', 'relation_table.id')
            ->selectRaw("COALESCE(relation_table.name, ?) AS name, ROUND(SUM(".self::NET_SQL.') , 2) AS total', [$fallback])
            ->groupBy('relation_table.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row): array => ['name' => $row->name, 'total' => (float) $row->total])
            ->all();
    }

    private function expenseList(Builder $query, mixed $orderBy, int $limit): array
    {
        $query->whereRaw(self::KIND_SQL." = 'expense'")
            ->leftJoin('vendors', 'expenses.vendor_id', '=', 'vendors.id')
            ->selectRaw(
                'expenses.id, expenses.expense_date, expenses.description, '.self::NET_SQL.' AS total, '.
                'COALESCE(parents.name, categories.name) AS category, vendors.name AS vendor'
            );

        $orderBy instanceof \Illuminate\Database\Query\Expression
            ? $query->orderByDesc($orderBy)
            : $query->orderByDesc($orderBy)->orderByDesc('expenses.id');

        return $query->limit($limit)->get()->map(fn ($row): array => [
            'id' => (int) $row->id,
            'date' => Carbon::parse($row->expense_date)->format('d/M/Y'),
            'description' => $row->description,
            'category' => $row->category,
            'vendor' => $row->vendor,
            'total' => round((float) $row->total, 2),
        ])->all();
    }

    private function savingsOpportunities(Builder $current, Builder $previous): array
    {
        $totals = function (Builder $query): Collection {
            return (clone $query)
                ->whereRaw(self::KIND_SQL." = 'expense'")
                ->where('expenses.nature', 'want')
                ->selectRaw('COALESCE(parents.name, categories.name) AS name, ROUND(SUM('.self::NET_SQL.'), 2) AS total')
                ->groupByRaw('COALESCE(parents.name, categories.name)')
                ->pluck('total', 'name');
        };

        $currentTotals = $totals($current);
        $previousTotals = $totals($previous);

        return $currentTotals->map(function ($amount, string $name) use ($previousTotals): array {
            $currentAmount = (float) $amount;
            $previousAmount = (float) $previousTotals->get($name, 0);
            $increase = round($currentAmount - $previousAmount, 2);
            return [
                'name' => $name,
                'current' => $currentAmount,
                'previous' => $previousAmount,
                'increase' => $increase,
                'percentage' => $previousAmount > 0 ? round(($increase / $previousAmount) * 100, 1) : null,
                'estimated_saving' => round(max(0, $increase), 2),
            ];
        })->filter(fn (array $row): bool => $row['increase'] > 0)
            ->sortByDesc('estimated_saving')->take(5)->values()->all();
    }

    private function budgetAlerts(int $userId, Carbon $month, Builder $base): array
    {
        return Budget::query()->with('category.parent')
            ->where('user_id', $userId)
            ->whereDate('month', $month->toDateString())
            ->orderBy('category_id')->get()
            ->map(function (Budget $budget) use ($base): array {
                $categoryIds = $budget->category->parent_id
                    ? collect([$budget->category_id])
                    : $budget->category->children()->pluck('id')->push($budget->category_id);
                $actual = round((float) (clone $base)
                    ->whereRaw(self::KIND_SQL." = 'expense'")
                    ->whereIn('expenses.category_id', $categoryIds)
                    ->sum(DB::raw(self::NET_SQL)), 2);
                $limit = (float) $budget->amount;
                $percentage = $limit > 0 ? round(($actual / $limit) * 100, 1) : 0;
                return [
                    'category' => $budget->category->full_name,
                    'limit' => $limit,
                    'actual' => $actual,
                    'remaining' => round($limit - $actual, 2),
                    'percentage' => $percentage,
                    'status' => $percentage >= 100 ? 'over' : ($percentage >= 80 ? 'warning' : 'safe'),
                ];
            })->sortByDesc('percentage')->values()->all();
    }

    private function buildStory(float $expense, array $categories, ?float $change, float $cashback, array $vendors, array $budgets): array
    {
        $story = ['Total spent: Rs. '.number_format($expense, 2).'.'];
        if ($categories !== []) {
            $story[] = 'Biggest category: '.$categories[0]['name'].' (Rs. '.number_format($categories[0]['total'], 2).').';
        }
        if ($change !== null) {
            $story[] = 'Spending '.($change > 0 ? 'increased' : 'decreased').' by '.number_format(abs($change), 1).'% vs last month.';
        }
        $story[] = 'Cashback earned: Rs. '.number_format($cashback, 2).'.';
        if ($vendors !== []) {
            $story[] = 'Highest payee: '.$vendors[0]['name'].' (Rs. '.number_format($vendors[0]['total'], 2).').';
        }
        if ($budgets !== []) {
            $safe = collect($budgets)->where('status', 'safe')->count();
            $story[] = "Within budget for {$safe} of ".count($budgets).' tracked categories.';
        }
        return $story;
    }

    private function quickAddLinks(): array
    {
        $targets = [
            'Milk' => ['milk'], 'Petrol' => ['petrol'], 'Vegetables' => ['vegetables'],
            'Medicine' => ['medicines', 'medicine'], 'Tea' => ['tea'], 'Grocery' => ['general grocery', 'grocery'],
        ];
        $categories = Category::query()->whereIn(DB::raw('LOWER(name)'), collect($targets)->flatten()->all())->get();
        return collect($targets)->map(function (array $names, string $label) use ($categories): array {
            $category = $categories->first(fn (Category $item): bool => in_array(strtolower($item->name), $names, true));
            return [
                'label' => $label,
                'url' => route('expense.create', array_filter(['category_id' => $category?->id, 'description' => $label])),
            ];
        })->values()->all();
    }

    private function healthScore(
        float $expenseTotal,
        float $wantTotal,
        ?float $changePercentage,
        array $budgets,
        float $unassignedVendorTotal
    ): array {
        $wantRatio = $expenseTotal > 0 ? ($wantTotal / $expenseTotal) * 100 : 0;
        $wantPoints = $wantRatio <= 20 ? 25 : ($wantRatio <= 30 ? 20 : ($wantRatio <= 40 ? 14 : 8));

        $trendPoints = $changePercentage === null
            ? 15
            : ($changePercentage <= -5 ? 25 : ($changePercentage <= 5 ? 20 : ($changePercentage <= 15 ? 12 : 5)));

        $budgetPoints = $budgets === []
            ? 18
            : (int) round((collect($budgets)->where('status', 'safe')->count() / count($budgets)) * 35);

        $vendorCoverage = $expenseTotal > 0 ? max(0, 1 - ($unassignedVendorTotal / $expenseTotal)) : 1;
        $trackingPoints = (int) round($vendorCoverage * 15);
        $score = max(0, min(100, $wantPoints + $trendPoints + $budgetPoints + $trackingPoints));
        $status = $score >= 80 ? 'Excellent' : ($score >= 60 ? 'Good' : ($score >= 40 ? 'Needs Improvement' : 'Critical'));

        return [
            'score' => $score,
            'status' => $status,
            'message' => $score >= 80 ? 'Your spending habits are on track.' : ($score >= 60 ? 'A few focused changes can improve your score.' : 'Review wants, budgets and untagged payees.'),
            'components' => [
                ['label' => 'Budget discipline', 'points' => $budgetPoints, 'max' => 35],
                ['label' => 'Want control', 'points' => $wantPoints, 'max' => 25],
                ['label' => 'Spending trend', 'points' => $trendPoints, 'max' => 25],
                ['label' => 'Data quality', 'points' => $trackingPoints, 'max' => 15],
            ],
        ];
    }

    private function daysForAverage(Carbon $start, Carbon $end): int
    {
        if ($start->isCurrentMonth()) {
            return max(1, min(now()->day, $end->day));
        }
        return $end->day;
    }

    private function money(Collection $values, string $key): float
    {
        return round((float) $values->get($key, 0), 2);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\ExpenseFor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function dashboard1()
    {
        $bodyCss = getAuthPageCss();
        return view('admin.dashboard1', compact('bodyCss'));
    }

    public function dashboard3()
    {
        $bodyCss = getAuthPageCss();
        return view('admin.dashboard1-new', compact('bodyCss'));
    }

    public function dashboard2()
    {
        $bodyCss = getAuthPageCss();
        return view('admin.dashboard2', compact('bodyCss'));
    }

    /**
     * Get dashboard summary
     */
    /**
     * Get dashboard summary statistics
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * Query Parameters:
     * - period: today, week, month, year, custom
     * - start_date: YYYY-MM-DD (for custom period)
     * - end_date: YYYY-MM-DD (for custom period)
     * 
     * Response:
     * {
     *   "success": true,
     *   "data": {
     *     "total_expenses": 5250.75,
     *     "total_count": 45,
     *     "average_expense": 116.68,
     *     "highest_expense": 500.00,
     *     "lowest_expense": 10.50,
     *     "period": "This Month",
     *     "date_range": "2024-01-01 to 2024-01-31"
     *   }
     * }
     */
    public function summary(Request $request): JsonResponse
    {
        try {
            $period = $request->query('period', 'month');
            $userId = auth()->id();

            // Get date range based on period
            [$startDate, $endDate] = $this->getDateRange($period, $request);

            // Build query
            $query = Expense::where('created_by', $userId)
                ->whereNull('deleted_at')
                ->whereBetween('expense_date', [$startDate, $endDate]);

            // Calculate statistics
            $totalExpenses = $query->sum('amount');
            $totalCount = $query->count();
            $averageExpense = $totalCount > 0 ? $totalExpenses / $totalCount : 0;
            $highestExpense = $query->max('amount') ?? 0;
            $lowestExpense = $query->min('amount') ?? 0;

            return response()->json([
                'success' => true,
                'message' => 'Dashboard summary retrieved successfully',
                'data' => [
                    'total_expenses' => number_format(round($totalExpenses, 2), 2, '.', ','),
                    'total_count' => $totalCount,
                    'average_expense' => round($averageExpense, 2),
                    'highest_expense' => round($highestExpense, 2),
                    'lowest_expense' => round($lowestExpense, 2),
                    'period' => $this->getPeriodLabel($period),
                    'date_range' => $this->formatDateRange($startDate, $endDate)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving dashboard summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get day-wise expense breakdown
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * Query Parameters:
     * - period: week, month, year
     * - month: MM (for specific month)
     * - year: YYYY (for specific year)
     * 
     * Response:
     * {
     *   "success": true,
     *   "data": {
     *     "labels": ["Day 1", "Day 2", ...],
     *     "datasets": [
     *       {
     *         "label": "Daily Expenses",
     *         "data": [100, 150, 200, ...],
     *         "total": 5250.75
     *       }
     *     ]
     *   }
     * }
     */
    public function dayWise(Request $request): JsonResponse
    {
        try {
            $period = $request->query('period', 'month');
            $userId = auth()->id();

            [$startDate, $endDate] = $this->getDateRange($period, $request);

            // Get day-wise expenses
            $expenses = Expense::where('expenses.created_by', $userId)
                ->whereNull('expenses.deleted_at')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(expense_date) as date'),
                    DB::raw('DAY(expense_date) as day'),
                    DB::raw('SUM(amount) as total'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('date', DB::raw('DAY(expense_date)')) // Add DAY() to GROUP BY
                ->orderBy('date', 'asc') // Order by the aliased column
                ->get();

            $labels = [];
            $data = [];
            $totalAmount = 0;

            foreach ($expenses as $expense) {
                // $labels[] = 'Day ' . $expense->day;
                $labels[] = $expense->date;
                $data[] = (float) $expense->total;
                $totalAmount += $expense->total;
            }

            return response()->json([
                'success' => true,
                'message' => 'Day-wise expenses retrieved successfully',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Daily Expenses',
                            'data' => $data,
                            'borderColor' => '#0052CC',
                            'backgroundColor' => 'rgba(0, 82, 204, 0.1)',
                            'tension' => 0.4,
                            'fill' => true
                        ]
                    ],
                    'total' => round($totalAmount, 2),
                    'average' => count($data) > 0 ? round($totalAmount / count($data), 2) : 0,
                    'period' => $this->getPeriodLabel($period)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving day-wise expenses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get category-wise expense breakdown
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * Query Parameters:
     * - period: today, week, month, year, custom
     * - start_date: YYYY-MM-DD
     * - end_date: YYYY-MM-DD
     * 
     * Response:
     * {
     *   "success": true,
     *   "data": {
     *     "labels": ["Office", "Travel", "Utilities"],
     *     "datasets": [
     *       {
     *         "label": "Amount Spent",
     *         "data": [1250.50, 2000.00, 800.00],
     *         "backgroundColor": ["#0052CC", "#FF6B6B", "#4ECDC4"],
     *         "total": 4050.50
     *       }
     *     ]
     *   }
     * }
     */
    public function categoryWiseNew(Request $request): JsonResponse
    {
        try {
            $period = $request->query('period', 'month');
            $userId = auth()->id();

            [$startDate, $endDate] = $this->getDateRange($period, $request);

            // Get category-wise expenses with explicit table references
            $expenses = Expense::select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(expenses.amount) as total'),
                DB::raw('COUNT(expenses.id) as count')
            )
                ->join('categories', 'expenses.category_id', '=', 'categories.id')
                ->where('expenses.created_by', $userId)  // Explicitly specify expenses.created_by
                ->whereNull('expenses.deleted_at')  // Explicitly specify expenses.deleted_at
                ->whereBetween('expenses.expense_date', [$startDate, $endDate])
                ->whereNull('categories.deleted_at')  // Also check categories.deleted_at if needed
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('total')
                ->get();

            $labels = [];
            $data = [];
            $colors = $this->getChartColors();
            $totalAmount = 0;

            foreach ($expenses as $index => $expense) {
                $labels[] = $expense->name;
                $data[] = (float) $expense->total;
                $totalAmount += $expense->total;
            }

            return response()->json([
                'success' => true,
                'message' => 'Category-wise expenses retrieved successfully',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Amount Spent',
                            'data' => $data,
                            'backgroundColor' => array_slice($colors, 0, count($labels)),
                            'borderColor' => '#fff',
                            'borderWidth' => 2
                        ]
                    ],
                    'total' => round($totalAmount, 2),
                    'count' => count($expenses),
                    'period' => $this->getPeriodLabel($period),
                    'details' => $expenses->map(function ($e) use ($totalAmount) {
                        return [
                            'category' => $e->name,
                            'amount' => round($e->total, 2),
                            'count' => $e->count,
                            'percentage' => $totalAmount > 0 ? round(($e->total / $totalAmount) * 100, 2) : 0
                        ];
                    })->toArray()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving category-wise expenses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function categoryWise(Request $request): JsonResponse
    {
        try {
            $period = $request->query('period', 'month');
            $userId = auth()->id();

            [$startDate, $endDate] = $this->getDateRange($period, $request);

            // Get category-wise expenses with category colors
            $expenses = Expense::select(
                'categories.id',
                'categories.name',
                'categories.color_code', // Add color if you have it
                DB::raw('SUM(expenses.amount) as total'),
                DB::raw('COUNT(expenses.id) as count')
            )
                ->join('categories', 'expenses.category_id', '=', 'categories.id')
                ->where('expenses.created_by', $userId)
                ->whereNull('expenses.deleted_at')
                ->whereBetween('expenses.expense_date', [$startDate, $endDate])
                ->whereNull('categories.deleted_at')
                ->groupBy('categories.id', 'categories.name', 'categories.color_code')
                ->orderByDesc('total')
                ->get();

            $labels = [];
            $data = [];
            $backgroundColors = [];
            $borderColors = [];
            $totalAmount = 0;

            foreach ($expenses as $index => $expense) {
                $labels[] = $expense->name;
                $data[] = (float) $expense->total;

                // Use category color if exists, otherwise use default colors
                if (!empty($expense->color)) {
                    $backgroundColors[] = $expense->color;
                    $borderColors[] = $this->adjustBrightness($expense->color, -0.2); // Darker border
                } else {
                    $defaultColors = $this->getChartColors();
                    $backgroundColors[] = $defaultColors[$index % count($defaultColors)];
                    $borderColors[] = '#fff';
                }

                $totalAmount += $expense->total;
            }

            return response()->json([
                'success' => true,
                'message' => 'Category-wise expenses retrieved successfully',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Amount Spent',
                            'data' => $data,
                            'backgroundColor' => $backgroundColors,
                            'borderColor' => $borderColors,
                            'borderWidth' => 2,
                            'hoverOffset' => 4 // Add hover effect
                        ]
                    ],
                    'total' => round($totalAmount, 2),
                    'formatted_total' => 'Rs. ' . number_format(round($totalAmount, 2), 2),
                    'count' => count($expenses),
                    'period' => $this->getPeriodLabel($period),
                    'date_range' => $this->formatDateRange($startDate, $endDate),
                    'details' => $expenses->map(function ($e) use ($totalAmount) {
                        return [
                            'id' => $e->id,
                            'category' => $e->name,
                            'color' => $e->color_code ?? null,
                            'amount' => round($e->total, 2),
                            'formatted_amount' => 'Rs. ' . number_format(round($e->total, 2), 2),
                            'count' => $e->count,
                            'percentage' => $totalAmount > 0 ? round(($e->total / $totalAmount) * 100, 2) : 0,
                            'avg_per_item' => $e->count > 0 ? round($e->total / $e->count, 2) : 0
                        ];
                    })->toArray()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving category-wise expenses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Helper method to adjust color brightness
    private function adjustBrightness($color, $amount)
    {
        $color = ltrim($color, '#');
        if (strlen($color) == 3) {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }

        $r = max(0, min(255, hexdec(substr($color, 0, 2)) + ($amount * 255)));
        $g = max(0, min(255, hexdec(substr($color, 2, 2)) + ($amount * 255)));
        $b = max(0, min(255, hexdec(substr($color, 4, 2)) + ($amount * 255)));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
    /**
     * Get monthly expense trend
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * Query Parameters:
     * - year: YYYY (default: current year)
     * - months: number of months to show (default: 12)
     * 
     * Response:
     * {
     *   "success": true,
     *   "data": {
     *     "labels": ["January", "February", ...],
     *     "datasets": [
     *       {
     *         "label": "Monthly Expenses",
     *         "data": [1500, 1750.75, 2000, ...],
     *         "total": 20000.00
     *       }
     *     ]
     *   }
     * }
     */
    public function monthlyTrend(Request $request): JsonResponse
    {
        try {
            $year = $request->query('year', now()->year);
            $months = $request->query('months', 12);
            $userId = auth()->id();

            $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->addMonths($months)->endOfMonth();

            // Get monthly expenses
            $expenses = Expense::where('created_by', $userId)
                ->whereNull('deleted_at')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->select(
                    DB::raw('MONTH(expense_date) as month'),
                    DB::raw('MONTHNAME(expense_date) as month_name'),
                    DB::raw('SUM(amount) as total'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy(DB::raw('MONTH(expense_date)'), DB::raw('MONTHNAME(expense_date)'))
                ->orderBy('month', 'asc')
                ->get();

            $labels = [];
            $data = [];
            $totalAmount = 0;

            foreach ($expenses as $expense) {
                $labels[] = $expense->month_name;
                $data[] = (float) $expense->total;
                $totalAmount += $expense->total;
            }

            return response()->json([
                'success' => true,
                'message' => 'Monthly trend retrieved successfully',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Monthly Expenses',
                            'data' => $data,
                            'borderColor' => '#0052CC',
                            'backgroundColor' => 'rgba(0, 82, 204, 0.1)',
                            'tension' => 0.4,
                            'fill' => true,
                            'pointRadius' => 5,
                            'pointBackgroundColor' => '#0052CC'
                        ]
                    ],
                    'total' => round($totalAmount, 2),
                    'average' => count($data) > 0 ? round($totalAmount / count($data), 2) : 0,
                    'year' => $year
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving monthly trend',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment method-wise expense breakdown
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * Response:
     * {
     *   "success": true,
     *   "data": {
     *     "labels": ["Credit Card", "Cash", "Bank Transfer"],
     *     "datasets": [
     *       {
     *         "label": "Payment Methods",
     *         "data": [3000, 2250.75, 0],
     *         "total": 5250.75
     *       }
     *     ]
     *   }
     * }
     */
    public function paymentMethodWise(Request $request): JsonResponse
    {
        try {
            $period = $request->query('period', 'month');
            $userId = auth()->id();

            [$startDate, $endDate] = $this->getDateRange($period, $request);

            // Get payment method-wise expenses
            $expenses = Expense::where('expenses.created_by', $userId)
                ->whereNull('expenses.deleted_at')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->join('payment_methods', 'expenses.payment_method_id', '=', 'payment_methods.id')
                ->select(
                    'payment_methods.id',
                    'payment_methods.name',
                    DB::raw('SUM(expenses.amount) as total'),
                    DB::raw('COUNT(expenses.id) as count')
                )
                ->groupBy('payment_methods.id', 'payment_methods.name')
                ->orderByDesc('total')
                ->get();

            $labels = [];
            $data = [];
            $colors = $this->getChartColors();
            $totalAmount = 0;

            foreach ($expenses as $expense) {
                $labels[] = $expense->name;
                $data[] = (float) $expense->total;
                $totalAmount += $expense->total;
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment method-wise expenses retrieved successfully',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Payment Methods',
                            'data' => $data,
                            'backgroundColor' => array_slice($colors, 0, count($labels)),
                            'borderColor' => '#fff',
                            'borderWidth' => 2
                        ]
                    ],
                    'total' => round($totalAmount, 2),
                    'count' => count($expenses),
                    'details' => $expenses->map(function ($e) use ($totalAmount) {
                        return [
                            'method' => $e->name,
                            'type' => $e->type,
                            'amount' => round($e->total, 2),
                            'count' => $e->count,
                            'percentage' => $totalAmount > 0 ? round(($e->total / $totalAmount) * 100, 2) : 0
                        ];
                    })->toArray()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payment method-wise expenses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get expense type-wise breakdown
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function expenseTypeWise(Request $request): JsonResponse
    {
        try {
            $period = $request->query('period', 'month');
            $userId = auth()->id();

            [$startDate, $endDate] = $this->getDateRange($period, $request);

            // Get expense type-wise expenses
            $expenses = Expense::where('expenses.created_by', $userId)
                ->whereNull('expenses.deleted_at')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->join('expense_fors', 'expenses.expense_for_id', '=', 'expense_fors.id')
                ->select(
                    'expense_fors.id',
                    'expense_fors.name',
                    DB::raw('SUM(expenses.amount) as total'),
                    DB::raw('COUNT(expenses.id) as count')
                )
                ->groupBy('expense_fors.id', 'expense_fors.name')
                ->orderByDesc('total')
                ->get();

            $labels = [];
            $data = [];
            $colors = $this->getChartColors();
            $totalAmount = 0;

            foreach ($expenses as $expense) {
                $labels[] = $expense->name;
                $data[] = (float) $expense->total;
                $totalAmount += $expense->total;
            }

            return response()->json([
                'success' => true,
                'message' => 'Expense type-wise expenses retrieved successfully',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Expense Types',
                            'data' => $data,
                            'backgroundColor' => array_slice($colors, 0, count($labels)),
                            'borderColor' => '#fff',
                            'borderWidth' => 2
                        ]
                    ],
                    'total' => round($totalAmount, 2),
                    'count' => count($expenses),
                    'details' => $expenses->map(function ($e) use ($totalAmount) {
                        return [
                            'type' => $e->name,
                            'amount' => round($e->total, 2),
                            'count' => $e->count,
                            'percentage' => $totalAmount > 0 ? round(($e->total / $totalAmount) * 100, 2) : 0
                        ];
                    })->toArray()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving expense type-wise expenses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get comparative analysis (current period vs previous period)
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * Query Parameters:
     * - period: week, month, year
     * 
     * Response:
     * {
     *   "success": true,
     *   "data": {
     *     "current_period": {
     *       "total": 5250.75,
     *       "count": 45,
     *       "average": 116.68
     *     },
     *     "previous_period": {
     *       "total": 4800.00,
     *       "count": 40,
     *       "average": 120.00
     *     },
     *     "comparison": {
     *       "difference": 450.75,
     *       "percentage_change": 9.39,
     *       "trend": "up"
     *     }
     *   }
     * }
     */
    public function comparison(Request $request): JsonResponse
    {
        try {
            $period = $request->query('period', 'month');
            $userId = auth()->id();

            [$currentStart, $currentEnd] = $this->getDateRange($period, $request);
            $periodDays = $currentStart->diffInDays($currentEnd);
            $previousStart = $currentStart->copy()->subDays($periodDays + 1);
            $previousEnd = $currentStart->copy()->subDay();

            // Current period
            $currentQuery = Expense::where('created_by', $userId)
                ->whereNull('deleted_at')
                ->whereBetween('expense_date', [$currentStart, $currentEnd]);

            $currentTotal = $currentQuery->sum('amount');
            $currentCount = $currentQuery->count();
            $currentAverage = $currentCount > 0 ? $currentTotal / $currentCount : 0;

            // Previous period
            $previousQuery = Expense::where('created_by', $userId)
                ->whereNull('deleted_at')
                ->whereBetween('expense_date', [$previousStart, $previousEnd]);

            $previousTotal = $previousQuery->sum('amount');
            $previousCount = $previousQuery->count();
            $previousAverage = $previousCount > 0 ? $previousTotal / $previousCount : 0;

            // Calculate comparison
            $difference = $currentTotal - $previousTotal;
            $percentageChange = $previousTotal > 0 ? ($difference / $previousTotal) * 100 : 0;
            $trend = $difference > 0 ? 'up' : ($difference < 0 ? 'down' : 'neutral');

            return response()->json([
                'success' => true,
                'message' => 'Comparison data retrieved successfully',
                'data' => [
                    'current_period' => [
                        'label' => 'Current ' . $this->getPeriodLabel($period),
                        'total' => round($currentTotal, 2),
                        'count' => $currentCount,
                        'average' => round($currentAverage, 2),
                        'date_range' => $currentStart->format('Y-m-d') . ' to ' . $currentEnd->format('Y-m-d')
                    ],
                    'previous_period' => [
                        'label' => 'Previous ' . $this->getPeriodLabel($period),
                        'total' => round($previousTotal, 2),
                        'count' => $previousCount,
                        'average' => round($previousAverage, 2),
                        'date_range' => $previousStart->format('Y-m-d') . ' to ' . $previousEnd->format('Y-m-d')
                    ],
                    'comparison' => [
                        'difference' => round($difference, 2),
                        'percentage_change' => round($percentageChange, 2),
                        'trend' => $trend,
                        'status' => $trend === 'up' ? 'increased' : ($trend === 'down' ? 'decreased' : 'unchanged')
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving comparison data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get complete dashboard data (all charts at once)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function complete(Request $request): JsonResponse
    {
        try {
            $period = $request->query('period', 'month');
            $userId = auth()->id();

            [$startDate, $endDate] = $this->getDateRange($period, $request);

            // Summary
            $query = Expense::where('created_by', $userId)
                ->whereNull('deleted_at')
                ->whereBetween('expense_date', [$startDate, $endDate]);

            $summary = [
                'total' => round($query->sum('amount'), 2),
                'count' => $query->count(),
                'average' => 0,
                'highest' => round($query->max('amount') ?? 0, 2),
                'lowest' => round($query->min('amount') ?? 0, 2)
            ];

            if ($summary['count'] > 0) {
                $summary['average'] = round($summary['total'] / $summary['count'], 2);
            }

            // Get all chart data by calling individual methods
            $categoryData = json_decode($this->categoryWise($request)->getContent(), true)['data'];
            $paymentData = json_decode($this->paymentMethodWise($request)->getContent(), true)['data'];
            $typeData = json_decode($this->expenseTypeWise($request)->getContent(), true)['data'];
            $monthlyData = json_decode($this->monthlyTrend($request)->getContent(), true)['data'];
            $dayData = json_decode($this->dayWise($request)->getContent(), true)['data'];
            $comparisonData = json_decode($this->comparison($request)->getContent(), true)['data'];

            return response()->json([
                'success' => true,
                'message' => 'Complete dashboard data retrieved successfully',
                'data' => [
                    'summary' => $summary,
                    'category_wise' => $categoryData,
                    'payment_method_wise' => $paymentData,
                    'expense_type_wise' => $typeData,
                    'monthly_trend' => $monthlyData,
                    'day_wise' => $dayData,
                    'comparison' => $comparisonData,
                    'period' => $this->getPeriodLabel($period),
                    'date_range' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving complete dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Get date range based on period
     * 
     * @param string $period
     * @param Request $request
     * @return array
     */
    private function getDateRange(string $period, Request $request): array
    {
        $endDate = now()->endOfDay();

        switch ($period) {
            case 'today':
                $startDate = now()->startOfDay();
                break;
            case 'week':
                $startDate = now()->startOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                break;
            case 'custom':
                $startDate = Carbon::createFromFormat('Y-m-d', $request->query('start_date', now()->toDateString()));
                $endDate = Carbon::createFromFormat('Y-m-d', $request->query('end_date', now()->toDateString()))->endOfDay();
                break;
            default:
                $startDate = now()->startOfMonth();
        }

        return [$startDate, $endDate];
    }

    /**
     * Helper: Get period label
     * 
     * @param string $period
     * @return string
     */
    private function getPeriodLabel(string $period): string
    {
        return match ($period) {
            'today' => 'Today',
            'week' => 'This Week',
            'month' => 'This Month',
            'year' => 'This Year',
            'custom' => 'Custom Period',
            default => 'This Month'
        };
    }

    public function dayWiseWithCategory(Request $request): JsonResponse
    {
        try {
            $period = $request->query('period', 'month');
            $userId = auth()->id();

            [$startDate, $endDate] = $this->getDateRange($period, $request);

            $expenses = Expense::select(
                DB::raw('DATE(expenses.expense_date) as date'),
                'categories.name as category',
                DB::raw('SUM(expenses.amount) as total')
            )
                ->join('categories', 'expenses.category_id', '=', 'categories.id')
                ->where('expenses.created_by', $userId)
                ->whereNull('expenses.deleted_at')
                ->whereNull('categories.deleted_at')
                ->whereBetween('expenses.expense_date', [$startDate, $endDate])
                ->groupBy('date', 'categories.name')
                ->orderBy('date', 'asc')
                ->get();

            


            $dates = [];
            $categoryData = [];
            $colors = $this->getChartColors();
            $total = 0;
            foreach ($expenses as $expense) {
                $dates[$expense->date] = true;
                $categoryData[$expense->category][$expense->date] = (float) $expense->total;
                $total = $total + (float) $expense->total;
            }

            $labels = array_keys($dates);

            $datasets = [];
            $colorIndex = 0;

            foreach ($categoryData as $category => $values) {

                // Ignore category if total is 0
                if (array_sum($values) == 0) {
                    continue;
                }

                $data = [];
                foreach ($labels as $date) {
                    $data[] = $values[$date] ?? 0;
                }

                $datasets[] = [
                    'label' => $category,
                    'data' => $data,
                    'backgroundColor' => $colors[$colorIndex++ % count($colors)],
                    'stack' => 'expenses'
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Day-wise category expenses retrieved successfully',
                'total' => $total,
                'data' => [
                    'labels' => $labels,
                    'datasets' => $datasets
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving expenses',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Helper: Get chart colors
     * 
     * @return array
     */
    private function getChartColors(): array
    {
        return [
            '#0052CC', // Primary Blue
            '#FF6B6B', // Red
            '#4ECDC4', // Teal
            '#45B7D1', // Light Blue
            '#FFA07A', // Light Salmon
            '#98D8C8', // Mint
            '#F7DC6F', // Yellow
            '#BB8FCE', // Purple
            '#85C1E2', // Sky Blue
            '#F8B88B', // Peach
            '#52C41A', // Green
            '#FA8072', // Salmon
        ];
    }

    /**
     * Get dashboard index
     */
    public function index(): JsonResponse
    {
        return $this->summary();
    }

    private function formatDateRange($startDate, $endDate): string
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // If same year
        if ($start->year === $end->year) {
            // If same month
            if ($start->month === $end->month) {
                return $start->format('M Y'); // "Dec 2025"
            }
            // Different months but same year
            return $start->format('M') . ' - ' . $end->format('M Y'); // "Jan - Apr 2019"
        }

        // Different years
        return $start->format('M Y') . ' - ' . $end->format('M Y'); // "Dec 2024 - Jan 2025"
    }
}

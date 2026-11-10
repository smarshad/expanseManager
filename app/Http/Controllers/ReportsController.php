<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\ExpenseFor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Services\SpendingInsightsService;

/**
 * ReportsController - Advanced Reporting and Export
 * 
 * Provides comprehensive reporting functionality:
 * - Detailed expense reports
 * - Category-wise reports
 * - Payment method reports
 * - Date range reports
 * - Custom filtering
 * - Export to CSV, PDF, Excel
 * - Summary statistics
 */
class ReportsController extends Controller
{
    /**
     * Get detailed expense report
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * Query Parameters:
     * - start_date: YYYY-MM-DD
     * - end_date: YYYY-MM-DD
     * - category_id: integer (optional)
     * - payment_method_id: integer (optional)
     * - expense_for_id: integer (optional)
     * - sort_by: amount, date, category (default: date)
     * - sort_order: asc, desc (default: desc)
     * - page: integer (default: 1)
     * - per_page: integer (default: 15)
     * 
     * Response:
     * {
     *   "success": true,
     *   "data": {
     *     "expenses": [
     *       {
     *         "id": 1,
     *         "description": "Office supplies",
     *         "amount": 150.50,
     *         "expense_date": "2024-01-15",
     *         "category": "Office",
     *         "type": "Business",
     *         "payment_method": "Credit Card",
     *         "notes": "Monthly office supplies"
     *       }
     *     ],
     *     "summary": {
     *       "total_expenses": 5250.75,
     *       "total_count": 45,
     *       "average_expense": 116.68,
     *       "highest_expense": 500.00,
     *       "lowest_expense": 10.50
     *     },
     *     "pagination": {
     *       "current_page": 1,
     *       "per_page": 15,
     *       "total": 45,
     *       "last_page": 3
     *     }
     *   }
     * }
     */
    public function index()
    {
        $bodyCss = getAuthPageCss();
        $categories = Category::with('parent')->orderBy('name')->get();
        $expenseFors = ExpenseFor::all();
        $paymentMethods = PaymentMethod::all();
        return view('admin.reports.index', compact('bodyCss', 'categories', 'paymentMethods', 'expenseFors'));
    }


    public function spendingInsights(
        Request $request,
        SpendingInsightsService $insights
    ): JsonResponse {
        $validated = $request->validate([
            "start_date" => ["nullable", "date_format:Y-m-d"],
            "end_date" => ["nullable", "date_format:Y-m-d"],
        ]);

        $startDate = isset($validated["start_date"])
            ? Carbon::createFromFormat("Y-m-d", $validated["start_date"])
            : now()->startOfMonth();
        $endDate = isset($validated["end_date"])
            ? Carbon::createFromFormat("Y-m-d", $validated["end_date"])
            : now();

        if ($endDate->lt($startDate) || $startDate->diffInDays($endDate) > 366) {
            return response()->json([
                "success" => false,
                "message" => "Date range must be ordered and no longer than 366 days.",
            ], 422);
        }

        return response()->json([
            "success" => true,
            "data" => $insights->generate(
                (int) $request->user()->id,
                $startDate,
                $endDate
            ),
        ]);
    }

    public function detailedReport(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            // $startDate = Carbon::createFromFormat('Y-m-d', $request->query('start_date', now()->subMonth()->toDateString()));
            // $endDate = Carbon::createFromFormat('Y-m-d', $request->query('end_date', now()->toDateString()))->endOfDay();
            $startDate = Carbon::createFromFormat('Y-m-d', $request->query('start_date'));
            $endDate   = Carbon::createFromFormat('Y-m-d', $request->query('end_date'));
            $noOfDays = $startDate->diffInDays($endDate) + 1;
            // Build query
            $query = Expense::where('created_by', $userId)
                ->whereNull('deleted_at')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->with('category', 'expenseFor', 'paymentMethod', 'account', 'paymentPlatform', 'vendor');

            // Apply filters
            if ($request->has('category_id') && $request->query('category_id')) {
                $query->where('category_id', $request->query('category_id'));
            }

            if ($request->has('payment_method_id') && $request->query('payment_method_id')) {
                $query->where('payment_method_id', $request->query('payment_method_id'));
            }

            if ($request->has('expense_for_id') && $request->query('expense_for_id')) {
                $query->where('expense_for_id', $request->query('expense_for_id'));
            }

            // Sorting
            $sortBy = $request->query('sort_by', 'expense_date');
            $sortOrder = $request->query('sort_order', 'desc');

            $sortMap = [
                'amount' => 'amount',
                'date' => 'expense_date',
                'category' => 'category_id',
                'description' => 'description'
            ];

            $sortColumn = $sortMap[$sortBy] ?? 'expense_date';
            $query->orderBy($sortColumn, $sortOrder);

            // Get summary before pagination
            $summaryQuery = clone $query;
            $totalExpenses = $summaryQuery->sum('amount');
            $totalCount = $summaryQuery->count();
            // $averageExpense = $totalCount > 0 ? $totalExpenses / $totalCount : 0;
            $averageExpense = $noOfDays > 0 ? $totalExpenses / $noOfDays : 0;
            $highestExpense = $summaryQuery->max('amount') ?? 0;
            $lowestExpense = $summaryQuery->min('amount') ?? 0;

            // Pagination
            $perPage = $request->query('per_page', 15);
            $expenses = $query->paginate($perPage);

            // Format response
            $formattedExpenses = $expenses->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'description' => $expense->description,
                    'cashback' => $expense->cashback,
                    'amount' => round($expense->amount, 2),
                    'expense_date' => $expense->expense_date->format('Y-m-d'),
                    'category' => $expense->category->name ?? 'N/A',
                    'type' => $expense->expenseFor->name ?? 'N/A',
                    'payment_method' => $expense->paymentMethod->name ?? 'N/A',
                    'payment_platform' => $expense->paymentPlatform->name ?? '-',
                    'account' => $expense->account->name ?? 'N/A',
                    'vendor' => $expense->vendor->name ?? '-',
                    'notes' => $expense->notes
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Detailed report retrieved successfully',
                'data' => [
                    'expenses' => $formattedExpenses,
                    'summary' => [
                        'total_expenses' => round($totalExpenses, 2),
                        'total_count' => $totalCount,
                        'noOfDays' => $noOfDays,
                        'average_expense' => round($averageExpense, 2),
                        'highest_expense' => round($highestExpense, 2),
                        'lowest_expense' => round($lowestExpense, 2)
                    ],
                    'pagination' => [
                        'current_page' => $expenses->currentPage(),
                        'per_page' => $expenses->perPage(),
                        'total' => $expenses->total(),
                        'last_page' => $expenses->lastPage()
                    ],
                    'filters' => [
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                        'category_id' => $request->query('category_id'),
                        'payment_method_id' => $request->query('payment_method_id'),
                        'expense_for_id' => $request->query('expense_for_id')
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving detailed report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get category-wise detailed report
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function categoryReport(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $startDate = Carbon::createFromFormat('Y-m-d', $request->query('start_date', now()->subMonth()->toDateString()));
            $endDate = Carbon::createFromFormat('Y-m-d', $request->query('end_date', now()->toDateString()))->endOfDay();

            $expenses = Expense::where('expenses.created_by', $userId)
                ->whereNull('expenses.deleted_at')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->join('categories', 'expenses.category_id', '=', 'categories.id')
                ->select(
                    'categories.id',
                    'categories.name',
                    DB::raw('COUNT(expenses.id) as count'),
                    DB::raw('SUM(expenses.amount) as total'),
                    DB::raw('AVG(expenses.amount) as average'),
                    DB::raw('MAX(expenses.amount) as highest'),
                    DB::raw('MIN(expenses.amount) as lowest')
                )
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('total')
                ->get();

            $totalAmount = $expenses->sum('total');

            $formattedData = $expenses->map(function ($item) use ($totalAmount) {
                return [
                    'category_id' => $item->id,
                    'category_name' => $item->name,
                    'count' => $item->count,
                    'total_amount' => round($item->total, 2),
                    'average_amount' => round($item->average, 2),
                    'highest_amount' => round($item->highest, 2),
                    'lowest_amount' => round($item->lowest, 2),
                    'percentage' => $totalAmount > 0 ? round(($item->total / $totalAmount) * 100, 2) : 0
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Category report retrieved successfully',
                'data' => [
                    'categories' => $formattedData,
                    'summary' => [
                        'total_amount' => round($totalAmount, 2),
                        'total_categories' => count($formattedData),
                        'average_per_category' => count($formattedData) > 0 ? round($totalAmount / count($formattedData), 2) : 0
                    ],
                    'date_range' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving category report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment method report
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function paymentMethodReport(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $startDate = Carbon::createFromFormat('Y-m-d', $request->query('start_date', now()->subMonth()->toDateString()));
            $endDate = Carbon::createFromFormat('Y-m-d', $request->query('end_date', now()->toDateString()))->endOfDay();

            $expenses = Expense::where('expenses.created_by', $userId)
                ->whereNull('expenses.deleted_at')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->join('payment_methods', 'expenses.payment_method_id', '=', 'payment_methods.id')
                ->select(
                    'payment_methods.id',
                    'payment_methods.name',
                    DB::raw('COUNT(expenses.id) as count'),
                    DB::raw('SUM(expenses.amount) as total'),
                    DB::raw('AVG(expenses.amount) as average')
                )
                ->groupBy('payment_methods.id', 'payment_methods.name')
                ->orderByDesc('total')
                ->get();

            $totalAmount = $expenses->sum('total');

            $formattedData = $expenses->map(function ($item) use ($totalAmount) {
                return [
                    'method_id' => $item->id,
                    'method_name' => $item->name,
                    'method_type' => $item->type,
                    'count' => $item->count,
                    'total_amount' => round($item->total, 2),
                    'average_amount' => round($item->average, 2),
                    'percentage' => $totalAmount > 0 ? round(($item->total / $totalAmount) * 100, 2) : 0
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Payment method report retrieved successfully',
                'data' => [
                    'payment_methods' => $formattedData,
                    'summary' => [
                        'total_amount' => round($totalAmount, 2),
                        'total_methods' => count($formattedData)
                    ],
                    'date_range' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payment method report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monthly summary report
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function monthlySummaryReport(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $year = $request->query('year', now()->year);

            $expenses = Expense::where('created_by', $userId)
                ->whereNull('deleted_at')
                ->whereYear('expense_date', $year)
                ->select(
                    DB::raw('MONTH(expense_date) as month'),
                    DB::raw('MONTHNAME(expense_date) as month_name'),
                    DB::raw('COUNT(expenses.id) as count'),
                    DB::raw('SUM(amount) as total'),
                    DB::raw('AVG(amount) as average'),
                    DB::raw('MAX(amount) as highest'),
                    DB::raw('MIN(amount) as lowest')
                )
                ->groupBy(DB::raw('MONTH(expense_date)'), DB::raw('MONTHNAME(expense_date)'))
                ->orderBy('month', 'asc')
                ->get();

            $totalAmount = $expenses->sum('total');

            $formattedData = $expenses->map(function ($item) use ($totalAmount) {
                return [
                    'month' => $item->month,
                    'month_name' => $item->month_name,
                    'count' => $item->count,
                    'total_amount' => round($item->total, 2),
                    'average_amount' => round($item->average, 2),
                    'highest_amount' => round($item->highest, 2),
                    'lowest_amount' => round($item->lowest, 2),
                    'percentage' => $totalAmount > 0 ? round(($item->total / $totalAmount) * 100, 2) : 0
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Monthly summary report retrieved successfully',
                'data' => [
                    'months' => $formattedData,
                    'summary' => [
                        'total_amount' => round($totalAmount, 2),
                        'average_per_month' => count($formattedData) > 0 ? round($totalAmount / count($formattedData), 2) : 0,
                        'highest_month' => $formattedData->sortByDesc('total')->first()['month_name'] ?? 'N/A',
                        'lowest_month' => $formattedData->sortBy('total')->first()['month_name'] ?? 'N/A'
                    ],
                    'year' => $year
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving monthly summary report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export expenses to CSV
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportCsv(Request $request)
    {
        try {
            $userId = auth()->id();
            $startDate = Carbon::createFromFormat('Y-m-d', $request->query('start_date', now()->subMonth()->toDateString()));
            $endDate = Carbon::createFromFormat('Y-m-d', $request->query('end_date', now()->toDateString()))->endOfDay();

            $expenses = Expense::where('created_by', $userId)
                ->whereNull('deleted_at')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->with('category', 'expenseFor', 'paymentMethod', 'account', 'paymentPlatform', 'vendor')
                ->orderBy('expense_date', 'desc')
                ->get();

            $filename = 'expenses_' . now()->format('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ];

            $callback = function () use ($expenses) {
                $file = fopen('php://output', 'w');

                // Write headers
                fputcsv($file, [
                    'ID',
                    'Description',
                    'Amount',
                    'Date',
                    'Category',
                    'Type',
                    'Payment Method',
                    'Notes'
                ]);

                // Write data
                foreach ($expenses as $expense) {
                    fputcsv($file, [
                        $expense->id,
                        $expense->description,
                        $expense->amount,
                        $expense->expense_date->format('Y-m-d'),
                        $expense->category->name ?? 'N/A',
                        $expense->expenseFor->name ?? 'N/A',
                        $expense->paymentMethod->name ?? 'N/A',
                        $expense->notes
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting to CSV',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export expenses to PDF
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request)
    {
        try {
            $userId = auth()->id();
            $startDate = Carbon::createFromFormat('Y-m-d', $request->query('start_date', now()->subMonth()->toDateString()));
            $endDate = Carbon::createFromFormat('Y-m-d', $request->query('end_date', now()->toDateString()))->endOfDay();

            $expenses = Expense::where('created_by', $userId)
                ->whereNull('deleted_at')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->with('category', 'expenseFor', 'paymentMethod', 'account', 'paymentPlatform', 'vendor')
                ->orderBy('expense_date', 'desc')
                ->get();

            $totalAmount = $expenses->sum('amount');

            $pdf = Pdf::loadView('reports.expenses-pdf', [
                'expenses' => $expenses,
                'totalAmount' => $totalAmount,
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);

            return $pdf->download('expenses_' . now()->format('Y-m-d_H-i-s') . '.pdf');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting to PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export expenses to Excel
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportExcel(Request $request)
    {
        try {
            $userId = auth()->id();
            $startDate = Carbon::createFromFormat('Y-m-d', $request->query('start_date', now()->subMonth()->toDateString()));
            $endDate = Carbon::createFromFormat('Y-m-d', $request->query('end_date', now()->toDateString()))->endOfDay();

            $expenses = Expense::where('created_by', $userId)
                ->whereNull('deleted_at')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->with('category', 'expenseFor', 'paymentMethod', 'account', 'paymentPlatform', 'vendor')
                ->orderBy('expense_date', 'desc')
                ->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $headers = ['ID', 'Description', 'Amount', 'Date', 'Category', 'Type', 'Payment Method', 'Notes'];
            foreach ($headers as $col => $header) {
                $sheet->setCellValue(chr(65 + $col) . '1', $header);
            }

            // Set data
            $row = 2;
            foreach ($expenses as $expense) {
                $sheet->setCellValue('A' . $row, $expense->id);
                $sheet->setCellValue('B' . $row, $expense->description);
                $sheet->setCellValue('C' . $row, $expense->amount);
                $sheet->setCellValue('D' . $row, $expense->expense_date->format('Y-m-d'));
                $sheet->setCellValue('E' . $row, $expense->category->name ?? 'N/A');
                $sheet->setCellValue('F' . $row, $expense->expenseFor->name ?? 'N/A');
                $sheet->setCellValue('G' . $row, $expense->paymentMethod->name ?? 'N/A');
                $sheet->setCellValue('H' . $row, $expense->notes);
                $row++;
            }

            // Auto-fit columns
            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $filename = 'expenses_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            $writer = new Xlsx($spreadsheet);

            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();

            return response($content, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting to Excel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get custom report with advanced filtering
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function customReport(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'category_id' => 'nullable|exists:categories,id',
                'payment_method_id' => 'nullable|exists:payment_methods,id',
                'expense_for_id' => 'nullable|exists:expense_fors,id',
                'min_amount' => 'nullable|numeric|min:0',
                'max_amount' => 'nullable|numeric|min:0',
                'search' => 'nullable|string|max:255',
                'status' => 'nullable|in:pending,approved,rejected',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
                'sort_by' => 'nullable|in:expense_date,amount,category,status',
                'sort_order' => 'nullable|in:asc,desc'
            ]);

            \Log::info('Custom Request', [
                $request,
            ]);
            $user = Auth::user();

            $query = Expense::query()
                ->where('created_by', $user->id)
                ->with(['category', 'expenseFor', 'paymentMethod', 'account', 'paymentPlatform', 'vendor', 'creator']);

            $query = $this->applyFilters($query, $validated);

            // ✅ Clone query BEFORE pagination
            $summaryQuery = clone $query;

            $sortBy = $validated['sort_by'] ?? 'expense_date';
            $sortOrder = $validated['sort_order'] ?? 'asc';
            $query->orderBy($sortBy, $sortOrder);
           
            $perPage = $validated['per_page'] ?? 15;

            // $expenses = $query->paginate($perPage);
            $expenses = $query->paginate($perPage, ['*'], 'page', $validated['page']);
            \Log::info('Custom Report Report FULL SQL', [
                'query' => $this->getFullSql($query),
            ]);
            // ✅ Summary from full dataset
            $summary = $this->calculateSummary($summaryQuery);

            return response()->json([
                'success' => true,
                'message' => 'Report generated successfully',
                'data' => [
                    'expenses' => $expenses->items(),
                    'summary' => $summary,
                    'pagination' => [
                        'current_page' => $expenses->currentPage(),
                        'per_page' => $expenses->perPage(),
                        'total' => $expenses->total(),
                        'last_page' => $expenses->lastPage(),
                        'from' => $expenses->firstItem(),
                        'to' => $expenses->lastItem(),
                        // ✅ IMPORTANT
                        'next_page_url' => $expenses->nextPageUrl(),
                        'prev_page_url' => $expenses->previousPageUrl(),
                    ]
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Report generation error', ['error' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Error generating report'
            ], 500);
        }
    }
    /**
     * Apply Filters to Query
     * 
     * @param $query
     * @param array $filters
     * @return mixed
     */
    private function applyFilters($query, array $filters)
    {
        // Date range filter
        if (!empty($filters['start_date'])) {
            $startDate = $filters['start_date'];
            $query->whereDate('expense_date', '>=', $startDate);
        }

        if (!empty($filters['end_date'])) {
            $endDate = $filters['end_date'];
            $query->whereDate('expense_date', '<=', $endDate);
        }

        // Category filter
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Payment method filter
        if (!empty($filters['payment_method_id'])) {
            $query->where('payment_method_id', $filters['payment_method_id']);
        }

        // Expense type filter
        if (!empty($filters['expense_for_id'])) {
            $query->where('expense_for_id', $filters['expense_for_id']);
        }

        // Amount range filter
        if (!empty($filters['min_amount'])) {
            $query->where('amount', '>=', $filters['min_amount']);
        }

        if (!empty($filters['max_amount'])) {
            $query->where('amount', '<=', $filters['max_amount']);
        }

        // Text search filter
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('description', 'like', $searchTerm)
                    ->orWhere('notes', 'like', $searchTerm);
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    /**
     * Calculate Summary Statistics
     * 
     * @param $query
     * @return array
     */
    private function calculateSummary($query): array
    {
        $clonedQuery = clone $query;

        $expenses = $clonedQuery->get();
        $count = $expenses->count();

        if ($count === 0) {
            return [
                'total_expenses' => 0,
                'total_count' => 0,
                'average_expense' => 0,
                'highest_expense' => 0,
                'lowest_expense' => 0,
                'total_by_category' => [],
                'total_by_payment_method' => [],
                'total_by_status' => []
            ];
        }

        $totalAmount = $expenses->sum('amount');
        $averageAmount = $count > 0 ? $totalAmount / $count : 0;
        $highestAmount = $expenses->max('amount');
        $lowestAmount = $expenses->min('amount');
        $cashBackAmount = $expenses->sum('cashback');

        // Group by category
        $byCategory = $expenses->groupBy('category.name')->map(function ($items) {
            return [
                'name' => $items->first()->category->name ?? 'Uncategorized',
                'total' => $items->sum('amount'),
                'count' => $items->count(),
                'average' => $items->sum('amount') / $items->count()
            ];
        })->values();

        // Group by payment method
        $byPaymentMethod = $expenses->groupBy('paymentMethod.name')->map(function ($items) {
            return [
                'name' => $items->first()->paymentMethod->name ?? 'Unknown',
                'total' => $items->sum('amount'),
                'count' => $items->count(),
                'average' => $items->sum('amount') / $items->count()
            ];
        })->values();

        // Group by status
        $byStatus = $expenses->groupBy('status')->map(function ($items) {
            return [
                'status' => $items->first()->status,
                'total' => $items->sum('amount'),
                'count' => $items->count(),
                'average' => $items->sum('amount') / $items->count()
            ];
        })->values();

        return [
            'total_expenses' => round($totalAmount, 2),
            'total_count' => $count,
            'average_expense' => round($averageAmount, 2),
            'highest_expense' => round($highestAmount, 2),
            'lowest_expense' => round($lowestAmount, 2),
            'cashback_amount' => round($cashBackAmount, 2),
            'total_by_category' => $byCategory,
            'total_by_payment_method' => $byPaymentMethod,
            'total_by_status' => $byStatus
        ];
    }


    private function getFullSql($query)
    {
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        foreach ($bindings as $binding) {
            if (is_string($binding)) {
                $binding = "'" . addslashes($binding) . "'";
            } elseif ($binding instanceof \DateTimeInterface) {
                $binding = "'" . $binding->format('Y-m-d H:i:s') . "'";
            } elseif (is_null($binding)) {
                $binding = 'NULL';
            }

            $sql = preg_replace('/\?/', $binding, $sql, 1);
        }

        return $sql;
    }
}

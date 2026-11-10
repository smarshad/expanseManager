<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Services\MonthEndDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonthEndDashboardController extends Controller
{
    public function index(
        Request $request,
        MonthEndDashboardService $dashboard
    ): View {
        $latestDate = Expense::query()
            ->where('created_by', $request->user()->id)
            ->max('expense_date');
        $defaultMonth = $latestDate
            ? substr($latestDate, 0, 7)
            : now()->format('Y-m');

        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);
        $month = $validated['month'] ?? $defaultMonth;

        $categories = Category::query()
            ->with('parent')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.month-end.index', [
            'dashboard' => $dashboard->generate(
                (int) $request->user()->id,
                $month
            ),
            'categories' => $categories,
        ]);
    }

    public function v2(
        Request $request,
        MonthEndDashboardService $dashboard
    ): View {
        $latestDate = Expense::query()->where('created_by', $request->user()->id)->max('expense_date');
        $defaultMonth = $latestDate ? substr($latestDate, 0, 7) : now()->format('Y-m');
        $validated = $request->validate(['month' => ['nullable', 'date_format:Y-m']]);
        $month = $validated['month'] ?? $defaultMonth;
        $categories = Category::query()->with('parent')->where('is_active', true)->orderBy('name')->get();

        return view('admin.month-end.v2', [
            'dashboard' => $dashboard->generate((int) $request->user()->id, $month),
            'categories' => $categories,
        ]);
    }

    public function cardDetails(
        Request $request,
        MonthEndDashboardService $dashboard,
        string $card
    ): \Illuminate\Http\JsonResponse {
        abort_unless(in_array($card, MonthEndDashboardService::CARDS, true), 404);
        $validated = $request->validate(['month' => ['required', 'date_format:Y-m']]);

        return response()->json(
            $dashboard->cardDetails((int) $request->user()->id, $validated['month'], $card)
        );
    }

    public function exportCard(
        Request $request,
        MonthEndDashboardService $dashboard,
        \App\Services\SimpleXlsxWriter $xlsx,
        string $card
    ): \Symfony\Component\HttpFoundation\BinaryFileResponse {
        abort_unless(in_array($card, MonthEndDashboardService::CARDS, true), 404);
        $validated = $request->validate(['month' => ['required', 'date_format:Y-m']]);
        $data = $dashboard->cardDetails((int) $request->user()->id, $validated['month'], $card, null);

        $rows = array_map(fn (array $r): array => [
            $r['date'], $r['description'], $r['vendor'], $r['category'], $r['subcategory'], $r['total'],
        ], $data['rows']);
        $rows[] = ['', '', '', '', 'Total', $data['total']];

        $path = $xlsx->build(
            $card.' '.$validated['month'],
            ['Date', 'Description', 'Vendor', 'Category', 'Sub Category', 'Amount'],
            $rows,
            [5]
        );

        return response()
            ->download($path, "month-end-{$card}-{$validated['month']}.xlsx", [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    public function storeBudget(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:1', 'max:100000000'],
        ]);

        Budget::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'category_id' => $validated['category_id'],
                'month' => $validated['month'].'-01',
            ],
            ['amount' => $validated['amount']]
        );

        return redirect()
            ->route('month-end.index', ['month' => $validated['month']])
            ->with('success', 'Monthly budget saved.');
    }
}

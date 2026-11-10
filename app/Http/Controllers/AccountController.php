<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\PaymentMethod;
use App\Models\PaymentPlatform;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    public function index() { return view('admin.account.index'); }

    public function list(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $accounts = Account::with(['paymentMethods:id,name', 'paymentPlatforms:id,name'])
            ->when(! $this->canSeeAllUsers($request), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')->paginate(max(1, $request->integer('per_page', 10)));

        return response()->json([
            'data' => collect($accounts->items())->map(fn (Account $account) => [
                'id' => $account->id,
                'name' => $account->name,
                'bank_name' => $account->bank_name,
                'account_type' => str($account->account_type)->replace('_', ' ')->title()->toString(),
                'opening_balance' => $account->opening_balance,
                'methods' => $account->paymentMethods->pluck('name')->implode(', '),
                'platforms' => $account->paymentPlatforms->pluck('name')->implode(', '),
                'is_active' => $account->is_active,
                'can' => ['update' => true, 'delete' => ! $account->expenses()->exists()],
            ]),
            'draw' => $request->integer('draw', 1),
            'recordsTotal' => $accounts->total(),
            'recordsFiltered' => $accounts->total(),
        ]);
    }

    public function create(Request $request) { return $this->form($request); }

    public function store(Request $request): JsonResponse
    {
        [$data, $methodIds, $platformIds] = $this->validated($request);
        $account = DB::transaction(function () use ($request, $data, $methodIds, $platformIds) {
            $account = Account::create($data + ['user_id' => $request->user()->id]);
            $account->paymentMethods()->sync($methodIds);
            $account->paymentPlatforms()->sync($platformIds);
            return $account;
        });

        return response()->json(['success' => true, 'message' => 'Account created successfully.', 'callback' => route('account.list'), 'data' => $account], 201);
    }

    public function edit(Request $request, Account $account)
    {
        $this->owner($request, $account);
        return $this->form($request, $account);
    }

    public function update(Request $request, Account $account): JsonResponse
    {
        $this->owner($request, $account);
        [$data, $methodIds, $platformIds] = $this->validated($request, $account);
        DB::transaction(function () use ($account, $data, $methodIds, $platformIds) {
            $account->update($data);
            $account->paymentMethods()->sync($methodIds);
            $account->paymentPlatforms()->sync($platformIds);
        });

        return response()->json(['success' => true, 'message' => 'Account updated successfully.', 'callback' => route('account.list')]);
    }

    public function destroy(Request $request, Account $account): JsonResponse
    {
        $this->owner($request, $account);
        if ($account->expenses()->exists()) {
            return response()->json(['success' => false, 'message' => 'Account is used by an expense.'], 409);
        }
        $account->delete();
        return response()->json(['success' => true, 'message' => 'Account deleted successfully.']);
    }

    private function form(Request $request, ?Account $account = null)
    {
        return view('admin.account._form', [
            'account' => $account,
            'paymentMethods' => PaymentMethod::orderBy('name')->get(),
            'platforms' => PaymentPlatform::with('paymentMethod:id,name')
                ->when(! $this->canSeeAllUsers($request), fn ($q) => $q->where('user_id', $request->user()->id))
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    private function validated(Request $request, ?Account $account = null): array
    {
        $userId = $account?->user_id ?? $request->user()->id;
        $platformExistsRule = Rule::exists('payment_platforms', 'id');
        if (! $this->canSeeAllUsers($request)) {
            $platformExistsRule->where('user_id', $userId);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('accounts')->where(fn ($q) => $q->where('user_id', $userId))->ignore($account?->id)],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'account_type' => ['required', Rule::in(['cash', 'bank', 'credit_card', 'wallet', 'other'])],
            'opening_balance' => ['required', 'numeric'],
            'is_active' => ['nullable', 'boolean'],
            'payment_method_ids' => ['required', 'array', 'min:1'],
            'payment_method_ids.*' => ['integer', 'exists:payment_methods,id'],
            'payment_platform_ids' => ['nullable', 'array'],
            'payment_platform_ids.*' => ['integer', $platformExistsRule],
        ]);

        $methodIds = array_map('intval', $validated['payment_method_ids']);
        $platformIds = array_map('intval', $validated['payment_platform_ids'] ?? []);
        $invalidPlatform = PaymentPlatform::whereIn('id', $platformIds)->whereNotIn('payment_method_id', $methodIds)->exists();
        if ($invalidPlatform) {
            throw ValidationException::withMessages(['payment_platform_ids' => 'Selected platform must match one of the account payment methods.']);
        }

        $data = collect($validated)->only(['name', 'bank_name', 'account_type', 'opening_balance'])->all();
        $data['is_active'] = $request->boolean('is_active');
        return [$data, $methodIds, $platformIds];
    }

    private function owner(Request $request, Account $account): void
    {
        abort_unless($this->canSeeAllUsers($request) || $account->user_id === $request->user()->id, 403, 'You cannot manage this account.');
    }

    private function canSeeAllUsers(Request $request): bool
    {
        return (bool) $request->user()?->isSuperAdmin();
    }
}

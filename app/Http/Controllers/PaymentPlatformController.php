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

class PaymentPlatformController extends Controller
{
    public function index() { return view('admin.paymentplatform.index'); }

    public function list(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $platforms = PaymentPlatform::with(['paymentMethod:id,name', 'accounts:id,name'])
            ->when(! $this->canSeeAllUsers($request), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')->paginate(max(1, $request->integer('per_page', 10)));

        return response()->json([
            'data' => collect($platforms->items())->map(fn (PaymentPlatform $platform) => [
                'id' => $platform->id,
                'name' => $platform->name,
                'method' => $platform->paymentMethod?->name,
                'accounts' => $platform->accounts->pluck('name')->implode(', '),
                'is_active' => $platform->is_active,
                'can' => ['update' => true, 'delete' => ! $platform->expenses()->exists()],
            ]),
            'draw' => $request->integer('draw', 1),
            'recordsTotal' => $platforms->total(),
            'recordsFiltered' => $platforms->total(),
        ]);
    }

    public function create(Request $request) { return $this->form($request); }

    public function store(Request $request): JsonResponse
    {
        [$data, $accountIds] = $this->validated($request);
        $platform = DB::transaction(function () use ($request, $data, $accountIds) {
            $platform = PaymentPlatform::create($data + ['user_id' => $request->user()->id]);
            $platform->accounts()->sync($accountIds);
            return $platform;
        });
        return response()->json(['success' => true, 'message' => 'Payment platform created successfully.', 'callback' => route('payment-platform.list'), 'data' => $platform], 201);
    }

    public function edit(Request $request, PaymentPlatform $paymentPlatform)
    {
        $this->owner($request, $paymentPlatform);
        return $this->form($request, $paymentPlatform);
    }

    public function update(Request $request, PaymentPlatform $paymentPlatform): JsonResponse
    {
        $this->owner($request, $paymentPlatform);
        [$data, $accountIds] = $this->validated($request, $paymentPlatform);
        DB::transaction(function () use ($paymentPlatform, $data, $accountIds) {
            $paymentPlatform->update($data);
            $paymentPlatform->accounts()->sync($accountIds);
        });
        return response()->json(['success' => true, 'message' => 'Payment platform updated successfully.', 'callback' => route('payment-platform.list')]);
    }

    public function destroy(Request $request, PaymentPlatform $paymentPlatform): JsonResponse
    {
        $this->owner($request, $paymentPlatform);
        if ($paymentPlatform->expenses()->exists()) {
            return response()->json(['success' => false, 'message' => 'Platform is used by an expense.'], 409);
        }
        $paymentPlatform->delete();
        return response()->json(['success' => true, 'message' => 'Payment platform deleted successfully.']);
    }

    private function form(Request $request, ?PaymentPlatform $paymentPlatform = null)
    {
        return view('admin.paymentplatform._form', [
            'paymentPlatform' => $paymentPlatform,
            'paymentMethods' => PaymentMethod::orderBy('name')->get(),
            'accounts' => Account::with('paymentMethods:id')
                ->when(! $this->canSeeAllUsers($request), fn ($q) => $q->where('user_id', $request->user()->id))
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    private function validated(Request $request, ?PaymentPlatform $platform = null): array
    {
        $userId = $platform?->user_id ?? $request->user()->id;
        $accountExistsRule = Rule::exists('accounts', 'id');
        if (! $this->canSeeAllUsers($request)) {
            $accountExistsRule->where('user_id', $userId);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('payment_platforms')->where(fn ($q) => $q->where('user_id', $userId)->where('payment_method_id', $request->integer('payment_method_id')))->ignore($platform?->id)],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'account_ids' => ['nullable', 'array'],
            'account_ids.*' => ['integer', $accountExistsRule],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $accountIds = array_map('intval', $validated['account_ids'] ?? []);
        $invalidAccount = Account::whereIn('id', $accountIds)->whereDoesntHave('paymentMethods', fn ($q) => $q->whereKey($validated['payment_method_id']))->exists();
        if ($invalidAccount) {
            throw ValidationException::withMessages(['account_ids' => 'Every selected account must support this payment method.']);
        }
        return [[
            'name' => $validated['name'],
            'payment_method_id' => $validated['payment_method_id'],
            'is_active' => $request->boolean('is_active'),
        ], $accountIds];
    }

    private function owner(Request $request, PaymentPlatform $platform): void
    {
        abort_unless($this->canSeeAllUsers($request) || $platform->user_id === $request->user()->id, 403, 'You cannot manage this payment platform.');
    }

    private function canSeeAllUsers(Request $request): bool
    {
        return (bool) $request->user()?->isSuperAdmin();
    }
}

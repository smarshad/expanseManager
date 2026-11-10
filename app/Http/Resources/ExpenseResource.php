<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = Auth::user();
        return [
            'id' => $this->id,
            'description' => $this->description,
            'amount' => $this->amount,
            'cashback' => $this->cashback ?? 0,
            'notes' => $this->notes,
            'is_recurring' => (bool) $this->is_recurring,
            'frequency' => $this->frequency,
            'nature' => $this->nature,
            'transaction_kind' => $this->transaction_kind,
            'file_path' => $this->file_path,
            'category_id' => $this->category_id,
            'expense_for_id' => $this->expense_for_id,
            'payment_method_id' => $this->payment_method_id,
            'account_id' => $this->account_id,
            'payment_platform_id' => $this->payment_platform_id,
            'vendor_id' => $this->vendor_id,
            'expense_date' => $this->expense_date->format('d/m/Y'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', fn () => ['name' => $this->creator->name]),
            'category' => $this->whenLoaded('category', fn () => ['name' => $this->category->full_name]),
            'expenseFor' => $this->whenLoaded('expenseFor', fn () => ['name' => $this->expenseFor->name]),
            'paymentMethod' => $this->whenLoaded('paymentMethod', fn () => ['name' => $this->paymentMethod->name]),
            'account' => $this->whenLoaded('account', fn () => ['id' => $this->account->id, 'name' => $this->account->name]),
            'paymentPlatform' => $this->whenLoaded('paymentPlatform', fn () => $this->paymentPlatform ? ['id' => $this->paymentPlatform->id, 'name' => $this->paymentPlatform->name] : null),
            'vendor' => $this->whenLoaded('vendor', fn () => $this->vendor ? ['id' => $this->vendor->id, 'name' => $this->vendor->name] : null),
            'can' => $this->when($user, [
                'update' => $user->can('update', $this->resource),
                'delete' => $user->can('delete', $this->resource),
                'show' => $user->can('show', $this->resource),
            ]),
        ];
    }
}

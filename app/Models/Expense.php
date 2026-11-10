<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'expense_for_id', 'payment_method_id', 'account_id',
        'payment_platform_id', 'vendor_id', 'expense_date', 'description',
        'amount', 'cashback', 'notes', 'is_recurring', 'frequency', 'nature', 'transaction_kind',
        'spending_nature', 'recurrence',
        'file_path', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'cashback' => 'decimal:2',
        'expense_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function expenseFor(): BelongsTo { return $this->belongsTo(ExpenseFor::class); }
    public function paymentMethod(): BelongsTo { return $this->belongsTo(PaymentMethod::class); }
    public function account(): BelongsTo { return $this->belongsTo(Account::class); }
    public function paymentPlatform(): BelongsTo { return $this->belongsTo(PaymentPlatform::class); }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }

    public function scopeForUser($query, User $user)
    {
        return $user->isSuperAdmin() ? $query : $query->where('created_by', $user->id);
    }

    public function scopeDateRange($query, $startDate, $endDate) { return $query->whereBetween('expense_date', [$startDate, $endDate]); }
    public function scopeByCategory($query, $categoryId) { return $query->where('category_id', $categoryId); }
    public function scopeByPaymentMethod($query, $paymentMethodId) { return $query->where('payment_method_id', $paymentMethodId); }
}

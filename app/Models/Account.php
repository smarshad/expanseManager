<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'name', 'bank_name', 'account_type', 'opening_balance', 'is_active'];

    protected $casts = ['opening_balance' => 'decimal:2', 'is_active' => 'boolean'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function paymentMethods(): BelongsToMany { return $this->belongsToMany(PaymentMethod::class); }
    public function paymentPlatforms(): BelongsToMany { return $this->belongsToMany(PaymentPlatform::class, 'account_payment_platform'); }
    public function expenses(): HasMany { return $this->hasMany(Expense::class); }
}

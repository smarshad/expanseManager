<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentPlatform extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'payment_method_id', 'name', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function paymentMethod(): BelongsTo { return $this->belongsTo(PaymentMethod::class); }
    public function accounts(): BelongsToMany { return $this->belongsToMany(Account::class, 'account_payment_platform'); }
    public function expenses(): HasMany { return $this->hasMany(Expense::class); }
}

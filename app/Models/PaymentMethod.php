<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'created_by'];

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function accounts(): BelongsToMany { return $this->belongsToMany(Account::class); }
    public function paymentPlatforms(): HasMany { return $this->hasMany(PaymentPlatform::class); }
}

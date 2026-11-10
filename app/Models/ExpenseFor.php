<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseFor extends Model
{
    use SoftDeletes;

    protected $table = 'expense_fors';

    protected $fillable = [
        'name',
        'color_code',
        'sort_order',
        'is_active',
        'description',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function creator():BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

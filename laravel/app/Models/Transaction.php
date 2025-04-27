<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Enums\PurchaseItemStatus;
use App\Enums\TransactionStatus;

class Transaction extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = TABLE_TRANSACTIONS;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['transaction_id', 'user_id', 'bet_amount', 'game_type', 'status'];

    /**
     * Scope a query to only proccesed transactions.
     */
    public function scopeProcessed(Builder $query): void
    {
        $query->where('status', TransactionStatus::PROCESSED->value);
    }
}

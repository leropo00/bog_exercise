<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserAccount extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = TABLE_USER_ACCOUNTS;

   /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['username', 'email', 'balance'];

   /**
     * Return available balance that user can bet, by substracting from total user balance, ammount from bets in progress.
     *
     * @param  float
     */
    public function getAvailableBalanceAttribute(): float
    {
        return $this->balance - $this->betted_amount;
    }
    
    /**
     * Apply scope, that user can bet certain ammount.
     *
     * @param  Builder   $query
     * @param  float|int   $betAmount
     * 
     */
    public function scopeBetAmountPossible(Builder $query, float|int $betAmount): void
    {
        $query->where(DB::raw('balance - betted_amount'), '>=', floatval($betAmount));
    }
}

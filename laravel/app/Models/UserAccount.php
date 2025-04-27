<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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

    // add attribute
    // available_balance
    public function getAvailableBalanceAttribute()
    {
        // TODO include money_in_bets here        
        return $this->balance;
    }
    
    public function scopeBetAmountPossible(Builder $query, float|int $value): void
    {
        // TODO include money_in_bets here
        $query->where('balance', '>=', floatval($value));
    }
}

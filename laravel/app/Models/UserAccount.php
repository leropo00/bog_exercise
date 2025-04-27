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

    public function getAvailableBalanceAttribute()
    {
        return $this->balance - $this->betted_amount;
    }
    
    public function scopeBetAmountPossible(Builder $query, float|int $value): void
    {
        $query->where(DB::raw('balance - betted_amount'), '>=', floatval($value));
    }
}

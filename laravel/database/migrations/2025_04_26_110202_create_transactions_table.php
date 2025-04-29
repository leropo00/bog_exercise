<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TransactionStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(TABLE_TRANSACTIONS, function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->foreignId('user_id')->constrained(
                table: TABLE_USER_ACCOUNTS, indexName: 'transactions_user_id'
            );
            $table->decimal('bet_amount', total: 10, places: 2);
            $table->string('game_type');
            $table->enum('status',array_column(TransactionStatus::cases(), 'value'))->default(TransactionStatus::PROCESSED->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(TABLE_TRANSACTIONS);
    }
};

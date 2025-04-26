<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BetTransactionsController;

Route::post('/create-account', [BetTransactionsController::class, 'createAccount']);
Route::post('/process-transaction', [BetTransactionsController::class, 'processTransaction']);

<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseCode;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Models\UserAccount;
use App\Services\GamesService;

class BetTransactionsController extends Controller
{
    // Constructor property promotion is used
    public function __construct(protected GamesService $gamesService){}

    public function createAccount(Request $request)
    {
        $transactionData = $request->validate([
            'username' => ['required', 'unique:' . TABLE_USER_ACCOUNTS . ',username', 'max:255'],
            'email' => ['required', 'email', 'unique:' . TABLE_USER_ACCOUNTS .',email', 'max:255'],
            // initial_balance is optional, since table definition has default value, nut it has to be at least smallest bet
            'initial_balance' => ['nullable', 'numeric', 'min:' . MIN_BET_VALUE],
        ]);
        $data = [
            'username' => $transactionData['username'],
            'email' => $transactionData['email'],
        ];
        if (Arr::has($transactionData, 'initial_balance')) {         
            $data['balance'] = $transactionData['initial_balance'];
        }

        $user = UserAccount::create($data);
        return response()->json([
            'user_id' => $user->id,
            'username' =>  $user->username,
            'email' =>  $user->email,
            'balance' =>  $user->available_balance,
        ], ResponseCode::HTTP_CREATED);
    }

    public function processTransaction(Request $request)
    {
        $transactionData = $request->validate([
            'user_id' => ['required', 'integer', 'exists:'. TABLE_USER_ACCOUNTS .',id'],
            'bet_amount' => ['required', 'numeric', 'min:' . MIN_BET_VALUE],
            'transaction_id' => ['required', 'max:255'],
            'game_type' => ['required', 'in:'.POSSIBLE_GAMES],
        ]);

        if (Transaction::where("transaction_id", $transactionData['transaction_id'])->processed()->first()) {
            return response()->json(['message' => 'transaction already processed'], ResponseCode::HTTP_BAD_REQUEST);
        }

        if (!UserAccount::where("id", $transactionData['user_id'])->betAmountPossible($transactionData['bet_amount'])->first()) {
            return response()->json(['message' => 'bet no longer possible'], ResponseCode::HTTP_BAD_REQUEST);
        }        

        $lock = Cache::lock("bet_transaction_lock_" . $transactionData['transaction_id'], 10); // Lock for 10 seconds

        if (!$lock->get()) {
            // transaction is in progress return conflict status
            return response()->json(['message' => 'Too many requests, please try again.'], ResponseCode::HTTP_TOO_MANY_REQUESTS);
        }
		

        $betMoneyDeducted = false;

        try {
            /*
                Money is already deducted here, by increment temporary field, until bet finishes.
                Temporary field is used for extra safety
                for additional protection against corrupted data there should be a cron job
                that would set this fields to 0, if user account wasn't active for a while, 
                with current implementation updated_at could be used
            */
            UserAccount::where("id", $transactionData['user_id'])->increment('betted_amount', $transactionData['bet_amount']);
            $betMoneyDeducted = true;

            $betWinnings = $this->gamesService->calculateBetWinnings($transactionData['bet_amount'],  $transactionData['game_type']);
            // winnings return 0, when bet was lost, balanceAmountChange amount will be negative then 
            $balanceAmountChange = $betWinnings - $transactionData['bet_amount'];

            DB::transaction(function () use ($transactionData, $balanceAmountChange)  {

                UserAccount::where("id", $transactionData['user_id'])->decrement('betted_amount', $transactionData['bet_amount']);

                if ($balanceAmountChange < 0) {
                    UserAccount::where("id", $transactionData['user_id'])->decrement('balance', -$balanceAmountChange);
                } 
                else if ($balanceAmountChange > 0) {
                    UserAccount::where("id", $transactionData['user_id'])->increment('balance', $balanceAmountChange);
                }

                $transactionData['status'] = TransactionStatus::PROCESSED->value;
                Transaction::create($transactionData);
            });
                

        } catch (Exception $e) {
            if ($betMoneyDeducted) {
                // revert back the bet money deducted
                UserAccount::where("id", $transactionData['user_id'])->decrement('betted_amount', $transactionData['bet_amount']);
            }

            $transactionData['status'] = TransactionStatus::ERROR_PROCESSING->value;
            Transaction::create($transactionData);
            // TODO add error fields inside the table maybe

            return response([
                "status" =>  "error",
                "message" =>  "System error occured while processing transaction",
                "transaction_id" =>  $transactionData['transaction_id'],
            ], ResponseCode::HTTP_INTERNAL_SERVER_ERROR);

        } finally {
            $lock->release();
        }

        return response([
                "status" =>  "success",
                "message" =>  "Transaction processed successfully",
                "transaction_id" =>  $transactionData['transaction_id'],
                "user_balance" => UserAccount::where("id", $transactionData['user_id'])->first()->available_balance,
            ], ResponseCode::HTTP_CREATED);
    }
}

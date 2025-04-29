<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseCode;
use App\Enums\TransactionStatus;
use App\Http\Resources\UserResource;
use App\Models\Transaction;
use App\Models\UserAccount;
use App\Services\GamesService;
use App\Traits\ApiStatusResponses;

class BetTransactionsController extends Controller
{
    use ApiStatusResponses;

    // Constructor property promotion is used
    public function __construct(protected GamesService $gamesService){}

    /**
     * Creates a user new account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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
        return (new UserResource($user))
            ->response()
            ->setStatusCode(ResponseCode::HTTP_CREATED);
    }

    /**
     * Process bet transaction and change user account balance according to bet outcome.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function processTransaction(Request $request)
    {
        $transactionData = $request->validate([
            'user_id' => ['required', 'integer', 'exists:'. TABLE_USER_ACCOUNTS .',id'],
            'bet_amount' => ['required', 'numeric', 'min:' . MIN_BET_VALUE],
            'transaction_id' => ['required', 'max:255'],
            'game_type' => ['required', 'in:'.POSSIBLE_GAMES],
        ]);

        if (Transaction::where("transaction_id", $transactionData['transaction_id'])->processed()->first()) {
            return $this->badRequestResponse(
                ['transaction_id' => $transactionData['transaction_id']], 
                PROCESS_TRANSACTION_MSG_ALREADY_PROCCESED,
            );
        }

        if (!UserAccount::where("id", $transactionData['user_id'])->betAmountPossible($transactionData['bet_amount'])->first()) {
            return $this->badRequestResponse(
                ['transaction_id' => $transactionData['transaction_id']], 
                PROCESS_TRANSACTION_MSG_BET_TOO_LARGE,
            );
        }        

        $lock = Cache::lock("bet_transaction_lock_" . $transactionData['transaction_id'], 30); 
        if (!$lock->get()) {
            return $this->conflictResponse(
                ['transaction_id' => $transactionData['transaction_id']], 
                PROCESS_TRANSACTION_MSG_ALREADY_IN_PROGRESS,
            );
        }
		
        $bettedAmountUpdated = false;
        try {
            /*
                Money is already deducted here, by incrementing betted_amount field, until the bet finishes.
                Temporary field is used for extra safety,
                for additional protection against corrupted data, there should be a cron job
                that would set this fields to 0, if user account wasn't active for a while, 
                with current implementation updated_at timestamp could be used.
            */
            UserAccount::where("id", $transactionData['user_id'])->increment('betted_amount', $transactionData['bet_amount']);
            $bettedAmountUpdated = true;

            $betWinnings = $this->gamesService->calculateBetWinnings($transactionData['bet_amount'],  $transactionData['game_type']);
            /*
                Since bet Winnings return 0, when bet is lost, balanceAmountChange amount will be negative then.
                By doing things this way, you could implement a game, where betted money would only be partialy lost.
                so for example you would bet 10.0, but have only 5.0 deducted when losing in certain conditions
            */
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
            
            $bettedAmountUpdated = false;

        } catch (Exception $e) {
            // revert back the bet money deducted
            if ($bettedAmountUpdated) {
                UserAccount::where("id", $transactionData['user_id'])->decrement('betted_amount', $transactionData['bet_amount']);
            }

            $transactionData['status'] = TransactionStatus::ERROR_PROCESSING->value;
            Transaction::create($transactionData);
            // TODO add error fields inside the table maybe

            /*  Important from php language documentaion
                https://www.php.net/manual/en/language.exceptions.php

                A return statement is encountered inside either the try or the catch blocks, 
                the finally block will still be executed.
                Moreover, the return statement is evaluated when encountered, 
                but the result will be returned after the finally block is executed
            */

            return $this->errorResponse(
                ['transaction_id' => $transactionData['transaction_id']], 
                PROCESS_TRANSACTION_MSG_SYSTEM_ERROR,
            );

        } finally {
            $lock->release();
        }

        return $this->createdResponse([
            "transaction_id" =>  $transactionData['transaction_id'],
            "user_balance" => UserAccount::where("id", $transactionData['user_id'])->first()->available_balance,
        ], PROCESS_TRANSACTION_MSG_TRANSACTION_SUCCESS);
    }
}

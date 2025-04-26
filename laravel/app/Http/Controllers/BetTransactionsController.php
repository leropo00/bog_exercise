<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseCode;
use App\Models\UserAccount;

class BetTransactionsController extends Controller
{
    public function createAccount(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required'],
            'email' => ['required', 'email'],
            // initial_balance is optional, since table definition has default value, it has to be at least smallest bet
            'initial_balance' => ['nullable', 'numeric', 'min:10.0'],
        ]);
        $data = [
            'username' => $validated['username'],
            'email' => $validated['email'],
        ];
        if (Arr::has($validated, 'initial_balance')) {         
            $data['balance'] = $validated['initial_balance'];
        }

        $user = UserAccount::create($data);
        return response()->json([
            'user_id' => $user->id,
            'username' =>  $user->username,
            'email' =>  $user->email,
            'balance' =>  $user->balance,

        ], ResponseCode::HTTP_CREATED);
    }

    public function processTransaction(Request $request)
    {
        return response(['implement' =>'todo'], ResponseCode::HTTP_OK);
    }
}

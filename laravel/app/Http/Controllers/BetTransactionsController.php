<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseCode;
use App\Enums\PurchaseItemStatus;
use App\Models\PurchaseItem;
use App\Models\PurchaseListEvent;

class BetTransactionsController extends Controller
{
    public function createAccount(Request $request)
    {
        return response(['implement' =>'todo'], ResponseCode::HTTP_OK);
    }

    public function processTransaction(Request $request)
    {
        return response(['implement' =>'todo'], ResponseCode::HTTP_OK);
    }
}

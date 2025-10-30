<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Resources\Wallet\GetBalanceResource;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * @param WalletService $wallet
     */
    public function __construct(
     private  readonly  WalletService $wallet
    )
    {
    }

    public function deposit(DepositRequest $r): JsonResponse {
        $res = $this->wallet->deposit($r->integer('user_id'), (string)$r->input('amount'), $r->input('comment'));
        return response()->json($res, 200);
    }

    public function withdraw(WithdrawRequest $r): JsonResponse {
        $res = $this->wallet->withdraw($r->integer('user_id'), (string)$r->input('amount'), $r->input('comment'));
        return response()->json($res, 200);
    }

    public function transfer(TransferRequest $r): JsonResponse {
        $res = $this->wallet->transfer($r->integer('from_user_id'), $r->integer('to_user_id'), (string)$r->input('amount'), $r->input('comment'));
        return response()->json($res, 200);
    }

    /**
     *  Получение баланса
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function balance(int $userId): JsonResponse {

        $res  = $this->wallet->getBalance($userId);

        return response()->json([
            'success' => true,
            'payload' => GetBalanceResource::make($res),
        ], 200);
    }
}

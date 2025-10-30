<?php

namespace App\Services;

use App\Models\Balance;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function deposit(int $userId, string $amount, ?string $comment): array
    {
        return DB::transaction(function () use ($userId, $amount, $comment) {
            $user = User::find($userId);
            if (!$user) abort(404, 'User not found');

            $balance = Balance::where('user_id', $userId)->lockForUpdate()->first();
            if (!$balance) $balance = Balance::create(['user_id' => $userId, 'amount' => 0]);

            $balance->amount = bcadd($balance->amount, $amount, 2);
            $balance->save();

            Transaction::create([
                'user_id' => $userId,
                'type' => 'deposit',
                'amount' => $amount,
                'comment' => $comment,
            ]);

            return ['user_id' => $userId, 'balance' => (float)$balance->amount];
        });
    }

    public function withdraw(int $userId, string $amount, ?string $comment): array
    {
        return DB::transaction(function () use ($userId, $amount, $comment) {
            $user = User::find($userId);
            if (!$user) abort(404, 'User not found');

            $balance = Balance::where('user_id', $userId)->lockForUpdate()->first();
            if (!$balance || bccomp($balance->amount, $amount, 2) < 0) abort(409, 'Insufficient funds');

            $balance->amount = bcsub($balance->amount, $amount, 2);
            $balance->save();

            Transaction::create([
                'user_id' => $userId,
                'type' => 'withdraw',
                'amount' => $amount,
                'comment' => $comment,
            ]);

            return ['user_id' => $userId, 'balance' => (float)$balance->amount];
        });
    }

    public function transfer(int $fromId, int $toId, string $amount, ?string $comment): array
    {
        if ($fromId === $toId) abort(422, 'Cannot transfer to self');

        return DB::transaction(function () use ($fromId, $toId, $amount, $comment) {
            $from = User::find($fromId);
            $to   = User::find($toId);
            if (!$from || !$to) abort(404, 'User not found');

            $fromBal = Balance::where('user_id', $fromId)->lockForUpdate()->first();
            if (!$fromBal || bccomp($fromBal->amount, $amount, 2) < 0) abort(409, 'Insufficient funds');

            $toBal = Balance::where('user_id', $toId)->lockForUpdate()->first();
            if (!$toBal) $toBal = Balance::create(['user_id' => $toId, 'amount' => 0]);

            $fromBal->amount = bcsub($fromBal->amount, $amount, 2);
            $fromBal->save();
            Transaction::create([
                'user_id' => $fromId,
                'type' => 'transfer_out',
                'amount' => $amount,
                'related_user_id' => $toId,
                'comment' => $comment,
            ]);

            $toBal->amount = bcadd($toBal->amount, $amount, 2);
            $toBal->save();
            Transaction::create([
                'user_id' => $toId,
                'type' => 'transfer_in',
                'amount' => $amount,
                'related_user_id' => $fromId,
                'comment' => $comment,
            ]);

            return [
                'from' => ['user_id' => $fromId, 'balance' => (float)$fromBal->amount],
                'to'   => ['user_id' => $toId, 'balance' => (float)$toBal->amount],
            ];
        });
    }

    public function getBalance(int $userId): array
    {
        $user = User::find($userId);
        if (!$user) abort(404, 'User not found');

        $balance = Balance::firstOrCreate(['user_id' => $userId], ['amount' => 0]);
        return ['user_id' => $userId, 'balance' => (float)$balance->amount];
    }
}

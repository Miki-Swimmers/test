<?php

use App\Http\Controllers\Api\WalletController;

Route::post('/deposit', [WalletController::class, 'deposit']);
Route::post('/withdraw', [WalletController::class, 'withdraw']);
Route::post('/transfer', [WalletController::class, 'transfer']);
Route::get('/balance/{userId}', [WalletController::class, 'balance']);

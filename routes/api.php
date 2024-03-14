<?php

use App\Http\Controllers\TransactionController;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::apiResource('transactions', TransactionController::class);

Route::get('balance', function (): JsonResponse {
    $totalBtc = Transaction::where('spent', false)->sum('amount_btc') - Transaction::where('spent', true)->sum('amount_btc');

    $btcEurResponse = Http::get('http://api-cryptopia.adca.sh/v1/prices/ticker?symbol=BTC%2FEUR')->json();

    $response = [
        'total_btc' => $totalBtc,
        'EUR/BTC' => (double)$btcEurResponse['data'][0]['value'],
        'EUR value' => $btcEurResponse['data'][0]['value'] * $totalBtc,
        'timestamp' => now(),
    ];

    return response()->json($response);
});

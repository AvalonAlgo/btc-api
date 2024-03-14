<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $transactions = Transaction::all();
        return response()->json($transactions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $totalBtc = Transaction::where('spent', false)->sum('amount_btc') - Transaction::where('spent', true)->sum('amount_btc');

        $eurAmount = $request->eurAmount;
        $spentBoolean = $request->spentBoolean;

        $btcToEur = Http::get('http://api-cryptopia.adca.sh/v1/prices/ticker?symbol=BTC%2FEUR')->json()['data'][0]['value'];
        $requestEurtoBtc = $eurAmount / $btcToEur;

        if ($requestEurtoBtc < 0.00001) {
            return response()->json(['res' => 'Failed! BTC amount too low!']);
        }

        if (!$spentBoolean) {
            $transaction = Transaction::create([
                'amount_btc' => $requestEurtoBtc,
                'spent' => $spentBoolean
            ]);
            return response()->json(['res' => 'Success! BTC added!', 'transaction' => $transaction]);
        } else if ($totalBtc - $requestEurtoBtc < 0) {
            return response()->json(['res' => 'Not enough BTC balance!']);
        } else if ($totalBtc - $requestEurtoBtc > 0 && $requestEurtoBtc >= 0.00001) {
            $transaction = Transaction::create([
                'amount_btc' => $requestEurtoBtc,
                'spent' => $spentBoolean
            ]);
            return response()->json(['res' => 'Success! BTC spent!', 'transaction' => $transaction]);
        }
        return response()->json(['res' => $requestEurtoBtc]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

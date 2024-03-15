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
        foreach ($transactions as $transaction) {
            $transaction->amount_btc = number_format($transaction->amount_btc, 5, ',');
        }
        return response()->json($transactions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $totalBtc = Transaction::where('spent', false)->sum('amount_btc');

        $eurAmount = $request->eurAmount;
        $spentBoolean = $request->spentBoolean;

        $btcToEur = Http::get('http://api-cryptopia.adca.sh/v1/prices/ticker?symbol=BTC%2FEUR')->json()['data'][0]['value'];
        $requestEurtoBtc = $eurAmount / $btcToEur;

        if ($requestEurtoBtc < 0.00001) {
            return response()->json(['res' => 'Failed! BTC amount too low!']);
        }

        if ($spentBoolean && ($totalBtc - $requestEurtoBtc <= 0)) {
            return response()->json(['res' => 'Not enough BTC balance!']);
        } else if ($spentBoolean && ($totalBtc - $requestEurtoBtc > 0)) {
            $transactions = Transaction::where('spent', false)->get();
            $sum = 0;
            foreach($transactions as $transaction) {
                $transaction->spent = true;
                $transaction->save();
                $sum += $transaction->amount_btc;
                if ($sum >= $requestEurtoBtc) {
                    break;
                }
            }

            $transaction = Transaction::create([
                'amount_btc' => floor(($sum - $requestEurtoBtc) * 100000) / 100000,
                'spent' => false
            ]);

            return response()->json(['res' => 'Success! BTC spent!', 'New transaction' => $transaction]);
        } else if (!$spentBoolean) {
            $transaction = Transaction::create([
                'amount_btc' => floor(($requestEurtoBtc) * 100000) / 100000,
                'spent' => false
            ]);

            return response()->json(['res' => 'Success! BTC added!', 'transaction' => $requestEurtoBtc]);
        }

        return response()->json(['res' => $requestEurtoBtc]);
    }
}

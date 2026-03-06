<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SavingWallet;
use App\Models\SavingTransaction;

class SavingTransactionController extends Controller
{
    // Get transaction history
    public function index(Request $request)
    {
        $wallet = SavingWallet::where('user_id', $request->user()->id)->first();

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        return response()->json($wallet->transactions);
    }

    // Deposit
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $wallet = SavingWallet::where('user_id', $request->user()->id)->first();

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        // Increase balance
        $wallet->balance += $request->amount;
        $wallet->save();

        // Record transaction
        $transaction = SavingTransaction::create([
            'saving_wallet_id' => $wallet->id,
            'type' => 'deposit',
            'amount' => $request->amount,
            'reference' => uniqid('DEP-')
        ]);

        return response()->json([
            'message' => 'Deposit successful',
            'balance' => $wallet->balance,
            'transaction' => $transaction
        ]);
    }

    // Withdraw
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $wallet = SavingWallet::where('user_id', $request->user()->id)->first();

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        if ($wallet->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        // Reduce balance
        $wallet->balance -= $request->amount;
        $wallet->save();

        // Record transaction
        $transaction = SavingTransaction::create([
            'saving_wallet_id' => $wallet->id,
            'type' => 'withdraw',
            'amount' => $request->amount,
            'reference' => uniqid('WTH-')
        ]);

        return response()->json([
            'message' => 'Withdrawal successful',
            'balance' => $wallet->balance,
            'transaction' => $transaction
        ]);
    }
}
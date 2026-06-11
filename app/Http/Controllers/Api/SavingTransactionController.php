<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SavingWallet;
use App\Models\SavingTransaction;

class SavingTransactionController extends Controller
{
    // ─────────────────────────────────────────────
    // GET TRANSACTION HISTORY
    // ─────────────────────────────────────────────
    public function index(Request $request)
    {
        // FIX: was ->first() which returned 404 for new users with no wallet yet
        // firstOrCreate auto-creates the wallet so the response is always 200
        $wallet = SavingWallet::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['name' => 'My Savings Wallet', 'balance' => 0]
        );

        $transactions = $wallet->transactions()
            ->latest()
            ->take(20)
            ->get();

        // FIX: wrap in 'transactions' key so Flutter AccountProvider can parse it
        return response()->json([
            'status'       => 'success',
            'transactions' => $transactions,
        ]);
    }

    // ─────────────────────────────────────────────
    // DEPOSIT
    // ─────────────────────────────────────────────
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        // FIX: was ->first() — use firstOrCreate so wallet is never missing
        $wallet = SavingWallet::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['name' => 'My Savings Wallet', 'balance' => 0]
        );

        $wallet->balance += $request->amount;
        $wallet->save();

        $transaction = SavingTransaction::create([
            'saving_wallet_id' => $wallet->id,
            'type'             => 'deposit',
            'amount'           => $request->amount,
            'reference'        => uniqid('DEP-'),
            'description'      => $request->description ?? 'Wallet deposit',
        ]);

        return response()->json([
            'status'      => 'success',
            'message'     => 'Deposit successful',
            'new_balance' => $wallet->balance,
            'transaction' => $transaction,
        ]);
    }

    // ─────────────────────────────────────────────
    // WITHDRAW
    // ─────────────────────────────────────────────
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        // FIX: was ->first() — use firstOrCreate so wallet is never missing
        $wallet = SavingWallet::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['name' => 'My Savings Wallet', 'balance' => 0]
        );

        if ($wallet->balance < $request->amount) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Insufficient balance',
            ], 400);
        }

        $wallet->balance -= $request->amount;
        $wallet->save();

        $transaction = SavingTransaction::create([
            'saving_wallet_id' => $wallet->id,
            'type'             => 'withdrawal',
            'amount'           => $request->amount,
            'reference'        => uniqid('WTH-'),
            'description'      => $request->description ?? 'Wallet withdrawal',
        ]);

        return response()->json([
            'status'      => 'success',
            'message'     => 'Withdrawal successful',
            'new_balance' => $wallet->balance,
            'transaction' => $transaction,
        ]);
    }
}
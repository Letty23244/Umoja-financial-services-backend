<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavingWallet;
use App\Models\SavingTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SavingWalletController extends Controller
{
    // GET /api/wallet
    public function index(): JsonResponse
    {
        $wallet = SavingWallet::where('user_id', Auth::id())->first();

        if (!$wallet) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No wallet found. Please create one.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $wallet,
        ]);
    }

    // POST /api/wallet/create
    public function store(Request $request): JsonResponse
    {
        $existing = SavingWallet::where('user_id', Auth::id())->exists();

        if ($existing) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You already have a savings wallet',
            ], 422);
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
        ]);

        $wallet = SavingWallet::create([
            'user_id' => Auth::id(),
            'name'    => $request->name ?? 'My Savings Wallet',
            'balance' => 0,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Savings wallet created successfully',
            'data'    => $wallet,
        ], 201);
    }

    // POST /api/wallet/deposit
    public function deposit(Request $request): JsonResponse
    {
        $request->validate([
            'amount'      => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $wallet = SavingWallet::where('user_id', Auth::id())->firstOrFail();

        $wallet->increment('balance', $request->amount);

        SavingTransaction::create([
            'saving_wallet_id' => $wallet->id,  // ← no 's'
            'amount'           => $request->amount,
            'type'             => 'deposit',
            'description'      => $request->description ?? 'Wallet deposit',
        ]);

        return response()->json([
            'status'      => 'success',
            'message'     => 'Deposit successful',
            'new_balance' => $wallet->fresh()->balance,
        ]);
    }

    // POST /api/wallet/withdraw
    public function withdraw(Request $request): JsonResponse
    {
        $wallet = SavingWallet::where('user_id', Auth::id())->firstOrFail();

        $request->validate([
            'amount'      => 'required|numeric|min:1|max:' . $wallet->balance,
            'description' => 'nullable|string|max:255',
        ]);

        if ($wallet->balance < $request->amount) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Insufficient balance',
            ], 422);
        }

        $wallet->decrement('balance', $request->amount);

        SavingTransaction::create([
            'saving_wallet_id' => $wallet->id,  // ← no 's'
            'amount'           => $request->amount,
            'type'             => 'withdrawal',
            'description'      => $request->description ?? 'Wallet withdrawal',
        ]);

        return response()->json([
            'status'      => 'success',
            'message'     => 'Withdrawal successful',
            'new_balance' => $wallet->fresh()->balance,
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Transaction;
use App\Models\PaymentMethod;

class PaymentController extends Controller
{
    /**
     * Deposit Money
     */
    public function deposit(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        $reference = Str::uuid();

        // Create transaction
        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'type' => 'deposit',
            'amount' => $request->amount,
            'status' => 'pending',
            'reference' => $reference,
            'payment_method_id' => $request->payment_method_id,
        ]);

        /*
        Here you will call MTN or Airtel API later
        */

        return response()->json([
            'status' => 'success',
            'message' => 'Deposit request started',
            'data' => $transaction,
        ]);
    }

    /**
     * Withdraw Money
     */
    public function withdraw(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        $reference = Str::uuid();

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'type' => 'withdrawal',
            'amount' => $request->amount,
            'status' => 'pending',
            'reference' => $reference,
            'payment_method_id' => $request->payment_method_id,
        ]);

        /*
        Later:
        Send withdrawal request to MTN/Airtel
        */

        return response()->json([
            'status' => 'success',
            'message' => 'Withdrawal request started',
            'data' => $transaction,
        ]);
    }

    /**
     * View User Transactions
     */
    public function transactions(): JsonResponse
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $transactions,
        ]);
    }
}
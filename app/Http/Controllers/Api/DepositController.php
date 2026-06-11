<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    // GET /api/deposits
    public function index(Request $request)
    {
        // Fetch deposits for the logged-in user
        $deposits = Deposit::where('user_id', $request->user()->id)->get();
        return response()->json($deposits);
    }

    // POST /api/deposits
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
        ]);

        $user = $request->user();

        DB::beginTransaction();

        try {
            // 1. Save Deposit record
            $deposit = Deposit::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'description' => $request->description ?? null,
            ]);

            // 2. Save TRANSACTION (STATEMENTS SCREEN)
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Deposit',
            ]);

            // 3. Save NOTIFICATION
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Deposit Successful',
                'message' => 'UGX ' . number_format($request->amount) . ' has been added to your wallet.',
            ]);

            // 4. 🚀 UPDATE THE ACTUAL BALANCE IN DATABASE
            // This increments the column on your users table immediately
            $user->increment('balance', $request->amount);

            DB::commit();

            // 5. ✨ FORMAT RESPONSE TO EXACTLY MATCH YOUR FLUTTER EXPECTATIONS
            return response()->json([
                'status' => 'success', // Your Flutter code explicitly checks for this
                'message' => 'Deposit successful',
                'deposit' => $deposit,
                'user' => [
                    'id' => $user->id,
                    'balance' => $user->balance, // Flutter reads this to update the wallet on the spot!
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Deposit failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
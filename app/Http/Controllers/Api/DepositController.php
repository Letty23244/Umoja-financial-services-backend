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
   // POST /api/deposits
    public function store(Request $request)
    {
        // 1. Validate incoming data safely
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
            'user_id' => 'nullable|integer', // Optional fallback for testing UI
        ]);

        // 2. Safely find the user without crashing
        // It checks the auth token first. If empty, it looks up the passed user_id.
        $user = $request->user() ?? \App\Models\User::find($request->user_id);

        // If no user is found anywhere, return a clean 401 error instead of a 500 crash
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication failed. Active user session or user_id not found.'
            ], 401);
        }

        DB::beginTransaction();

        try {
            // 3. Save Deposit record
            $deposit = Deposit::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'description' => $request->description ?? null,
            ]);

            // 4. Save TRANSACTION (STATEMENTS SCREEN)
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Deposit',
            ]);

            // 5. Save NOTIFICATION
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Deposit Successful',
                'message' => 'UGX ' . number_format($request->amount) . ' has been added to your wallet.',
                 'type' => 'deposit',
            ]);

            // 6. UPDATE THE ACTUAL BALANCE IN DATABASE
            $user->increment('balance', $request->amount);

            DB::commit();

            // 7. RETURN CLEAN JSON RESPONSE FOR FLUTTER
            return response()->json([
                'status' => 'success',
                'message' => 'Deposit successful',
                'deposit' => $deposit,
                'user' => [
                    'id' => $user->id,
                    'balance' => $user->balance, 
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Deposit processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
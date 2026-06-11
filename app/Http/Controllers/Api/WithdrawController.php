<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Withdraw;
use App\Http\Controllers\Controller; 
use App\Models\Transaction;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class WithdrawController extends Controller
{
    /**
     * GET /api/withdraws
     * Fetch all withdraws for the logged-in user
     */
    public function index(Request $request): JsonResponse
    {
        $withdraws = Withdraw::where('user_id', $request->user()->id)->get();
        return response()->json($withdraws);
    }

    /**
     * POST /api/withdraws
     * Handle incoming withdrawal requests
     */
    public function store(Request $request): JsonResponse
    {
        // 1. Validate incoming data safely
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|string', 
            'description' => 'nullable|string',
            'user_id' => 'nullable|integer', // Optional fallback for testing UI
        ]);

        // 2. Safely find the user without crashing
        // Checks the auth token first. If empty, it looks up the passed user_id.
        $user = $request->user() ?? \App\Models\User::find($request->user_id);

        // If no user is found, return a clean 401 error instead of a 500 crash
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication failed. Active user session or user_id not found.'
            ], 401);
        }

        // 3. BALANCE CHECK 
        if ($user->balance < $request->amount) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient account balance for this withdrawal.'
            ], 400); // 400 Bad Request
        }

        DB::beginTransaction();

        try {
            // 4. DEDUCT THE USER'S BALANCE 
            $user->balance -= $request->amount;
            $user->save();

            // 5. CREATE WITHDRAWAL RECORD 
            $withdraw = Withdraw::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'method' => $request->method, 
                'description' => $request->description ?? null,
            ]);

            // 6. CREATE TRANSACTION STATEMENT 
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Withdrawal via ' . $request->method,
            ]);

            // 7. CREATE NOTIFICATION (With custom 'general' type mapping)
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Withdrawal Successful',
                'message' => 'UGX ' . number_format($request->amount) . ' has been withdrawn.',
                'type' => 'general',
            ]);

            DB::commit();

            // 8. RETURN CLEAN JSON RESPONSE
            // Contains both response keys to satisfy the frontend map parsing pattern
            return response()->json([
                'status' => 'success',
                'message' => 'Withdraw successful',
                'withdraw' => $withdraw, 
                'new_balance' => $user->balance, // Maps directly to standard AuthService parsing fields
                'user' => [
                    'id' => $user->id,
                    'balance' => $user->balance, 
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Withdraw processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
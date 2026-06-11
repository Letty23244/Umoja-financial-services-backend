<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Withdraw;
use App\Http\Controllers\Controller; 
use App\Models\Transaction;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class WithdrawController extends Controller
{
    // GET /api/withdraws
    public function index(Request $request)
    {
        // Fetch all withdraws for the logged-in user
        $withdraws = Withdraw::where('user_id', $request->user()->id)->get();
        return response()->json($withdraws);
    }

    // POST /api/withdraws
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|string', // Ensure the method passed from Flutter is captured
            'description' => 'nullable|string',
        ]);

        $user = $request->user();

        // ── 1. BALANCE CHECK ─────────────────────────────────
        // Check if the user has enough funds before starting the process
        if ($user->balance < $request->amount) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient account balance for this withdrawal.'
            ], 400); // 400 Bad Request
        }

        DB::beginTransaction();

        try {
            // ── 2. DEDUCT THE USER'S BALANCE ───────────────────
            // This updates the actual live metric column inside Filament dashboard
            $user->balance -= $request->amount;
            $user->save();

            // ── 3. CREATE WITHDRAWAL RECORD ────────────────────
            $withdraw = Withdraw::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'method' => $request->method, // Saved the selected method (e.g., Mobile Money)
                'description' => $request->description ?? null,
            ]);

            // ── 4. CREATE TRANSACTION STATEMENT ────────────────
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Withdrawal via ' . $request->method,
            ]);

            // ── 5. CREATE NOTIFICATION ─────────────────────────
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Withdrawal Successful',
                'message' => 'UGX ' . number_format($request->amount) . ' has been withdrawn.',
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Withdraw successful',
                'withdraw' => $withdraw
                'new_balance' => $user->balance
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Withdraw failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
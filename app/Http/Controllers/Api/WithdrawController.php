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
        'description' => 'nullable|string',
    ]);

    $user = $request->user();

    DB::beginTransaction();

    try {

        // 1. Create withdraw record
        $withdraw = Withdraw::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'description' => $request->description ?? null,
        ]);

        // 2. Create STATEMENT (IMPORTANT)
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'withdrawal',
            'amount' => $request->amount,
            'description' => $request->description ?? 'Withdrawal',
        ]);

        // 3. Create NOTIFICATION
        Notification::create([
            'user_id' => $user->id,
            'title' => 'Withdrawal Successful',
            'message' => 'UGX ' . number_format($request->amount) . ' has been withdrawn.',
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Withdraw successful',
            'withdraw' => $withdraw
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Withdraw failed',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
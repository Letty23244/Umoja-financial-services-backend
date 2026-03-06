<?php


namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Withdraw;
use App\Http\Controllers\Controller; 

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

        $withdraw = Withdraw::create([
            'user_id' => $request->user()->id,
            'amount' => $request->amount,
            'description' => $request->description ?? null,
        ]);

        return response()->json([
            'message' => 'Withdraw created successfully',
            'withdraw' => $withdraw
        ]);
    }
    public function update(Request $request, $id)
{
    $withdraw = Withdraw::where('id', $id)
                        ->where('user_id', $request->user()->id)
                        ->first();

    if (!$withdraw) {
        return response()->json(['message' => 'Withdraw not found or not yours'], 404);
    }

    $request->validate([
        'amount' => 'sometimes|required|numeric|min:1',
        'description' => 'nullable|string',
    ]);

    $withdraw->update($request->only(['amount', 'description']));

    return response()->json([
        'message' => 'Withdraw updated successfully',
        'withdraw' => $withdraw
    ]);
}

    // DELETE /api/withdraws/{id}
    public function destroy(Request $request, $id)
    {
        $withdraw = Withdraw::where('id', $id)
                            ->where('user_id', $request->user()->id)
                            ->first();

        if (!$withdraw) {
            return response()->json([
                'message' => 'Withdraw not found or not yours'
            ], 404);
        }

        $withdraw->delete();

        return response()->json([
            'message' => 'Withdraw deleted successfully'
        ]);
    }
}
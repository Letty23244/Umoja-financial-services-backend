<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use App\Models\Deposit;

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

        $deposit = Deposit::create([
            'user_id' => $request->user()->id,  // use logged-in user
            'amount' => $request->amount,
            'description' => $request->description ?? null,
        ]);

        return response()->json([
            'message' => 'Deposit created successfully',
            'deposit' => $deposit
        ]);
    }
    public function update(Request $request, $id)
{
    $deposit = Deposit::where('id', $id)
                      ->where('user_id', $request->user()->id)
                      ->first();

    if (!$deposit) {
        return response()->json(['message' => 'Deposit not found or not yours'], 404);
    }

    $request->validate([
        'amount' => 'sometimes|required|numeric|min:1',
        'description' => 'nullable|string',
    ]);

    $deposit->update($request->only(['amount', 'description']));

    return response()->json([
        'message' => 'Deposit updated successfully',
        'deposit' => $deposit
    ]);
}

    // DELETE /api/deposits/{id}
    public function destroy(Request $request, $id)
    {
        $deposit = Deposit::where('id', $id)
                          ->where('user_id', $request->user()->id)
                          ->first();

        if (!$deposit) {
            return response()->json([
                'message' => 'Deposit not found or not yours'
            ], 404);
        }

        $deposit->delete();

        return response()->json([
            'message' => 'Deposit deleted successfully'
        ]);
    }
}
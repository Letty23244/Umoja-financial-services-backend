<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutoSavings;
use App\Models\SavingWallet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AutoSavingsController extends Controller
{
    // GET /api/auto-savings
    public function index(): JsonResponse
    {
        $autoSavings = AutoSavings::where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $autoSavings,
        ]);
    }

    // POST /api/auto-savings
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'saving_wallet_id'  => 'required|integer',
            'name'              => 'required|string|max:255',
            'amount'            => 'required|numeric|min:1',
            'frequency'         => 'required|in:daily,weekly,monthly',
            'payment_method'    => 'required|in:mobile_money,bank_transfer',
            'payment_reference' => 'required|string',
        ]);

        // Manually check wallet
        $wallet = SavingWallet::where('user_id', Auth::id())
            ->where('id', $request->saving_wallet_id)
            ->first();

        if (!$wallet) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Wallet not found',
            ], 404);
        }

        $autoSaving = AutoSavings::create([
            'user_id'             => Auth::id(),
            'saving_wallet_id'    => $wallet->id,
            'name'                => $request->name,
            'amount'              => $request->amount,
            'frequency'           => $request->frequency,
            'next_deduction_date' => match ($request->frequency) {
                'daily'   => now()->addDay()->toDateString(),
                'weekly'  => now()->addWeek()->toDateString(),
                'monthly' => now()->addMonth()->toDateString(),
            },
            'payment_method'    => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'status'            => 'active',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Auto savings set up successfully',
            'data'    => $autoSaving,
        ], 201);
    }

    // PUT /api/auto-savings/{id}/pause
    public function pause($id): JsonResponse
    {
        $autoSaving = AutoSavings::where('user_id', Auth::id())->findOrFail($id);
        $autoSaving->update(['status' => 'paused']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Auto savings paused',
        ]);
    }

    // PUT /api/auto-savings/{id}/resume
    public function resume($id): JsonResponse
    {
        $autoSaving = AutoSavings::where('user_id', Auth::id())->findOrFail($id);
        $autoSaving->update(['status' => 'active']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Auto savings resumed',
        ]);
    }

    // DELETE /api/auto-savings/{id}
    public function destroy($id): JsonResponse
    {
        $autoSaving = AutoSavings::where('user_id', Auth::id())->findOrFail($id);
        $autoSaving->update(['status' => 'cancelled']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Auto savings cancelled',
        ]);
    }
}
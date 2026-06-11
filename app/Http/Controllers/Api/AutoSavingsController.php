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
        // ✅ VALIDATION (FIXED)
        $request->validate([
            'saving_wallet_id'  => 'required|integer',
            'name'              => 'required|string|max:255',
            'amount'            => 'required|numeric|min:1',
            'frequency'         => 'required|in:daily,weekly,monthly',
            'payment_method'    => 'required|in:mobile_money,bank_transfer',
        ]);

        // ✅ VERIFY WALLET BELONGS TO USER
        $wallet = SavingWallet::where('id', $request->saving_wallet_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$wallet) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Wallet not found',
            ], 404);
        }

        // ✅ CREATE AUTO SAVING
        $autoSaving = AutoSavings::create([
            'user_id'             => Auth::id(),
            'saving_wallet_id'    => $wallet->id,
            'name'                => $request->name,
            'amount'              => $request->amount,
            'frequency'           => $request->frequency,

            // ✅ AUTO CALCULATED NEXT DATE
            'next_deduction_date' => match ($request->frequency) {
                'daily'   => now()->addDay()->toDateString(),
                'weekly'  => now()->addWeek()->toDateString(),
                'monthly' => now()->addMonth()->toDateString(),
            },

            'payment_method'      => $request->payment_method,

            // ✅ GENERATED IN BACKEND (NO FLUTTER NEEDED)
            'payment_reference'   => 'AUTO-' . now()->timestamp . rand(1000, 9999),

            'status'              => 'active',
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
        $autoSaving = AutoSavings::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $autoSaving->update(['status' => 'paused']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Auto savings paused',
        ]);
    }

    // PUT /api/auto-savings/{id}/resume
    public function resume($id): JsonResponse
    {
        $autoSaving = AutoSavings::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $autoSaving->update(['status' => 'active']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Auto savings resumed',
        ]);
    }

    // DELETE /api/auto-savings/{id}
    public function destroy($id): JsonResponse
    {
        $autoSaving = AutoSavings::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $autoSaving->update(['status' => 'cancelled']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Auto savings cancelled',
        ]);
    }
}
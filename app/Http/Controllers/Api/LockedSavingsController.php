<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LockedSavings;
use App\Models\SavingWallet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LockedSavingsController extends Controller
{
    // GET /api/locked-savings
    public function index(): JsonResponse
    {
        $savings = LockedSavings::where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(function ($saving) {
                $saving->has_matured     = $saving->hasMatured();
                $saving->interest_earned = $saving->interest_earned;
                $saving->maturity_amount = $saving->maturity_amount;
                return $saving;
            });

        return response()->json([
            'status' => 'success',
            'data'   => $savings,
        ]);
    }

    // POST /api/locked-savings
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'saving_wallet_id'    => 'required|integer',
            'name'                => 'required|string|max:255',
            'amount'              => 'required|numeric|min:1000',
            'lock_duration_years' => 'required|integer|min:1|max:5',
        ]);

        $wallet = SavingWallet::where('user_id', Auth::id())
            ->where('id', $request->saving_wallet_id)
            ->first();

        if (!$wallet) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Wallet not found',
            ], 404);
        }

        // Check sufficient balance
        if ($wallet->balance < $request->amount) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Insufficient wallet balance. Current balance: UGX ' . number_format($wallet->balance, 2),
            ], 422);
        }

        // Deduct from wallet
        $wallet->decrement('balance', $request->amount);

        $lockedSaving = LockedSavings::create([
            'user_id'             => Auth::id(),
            'saving_wallet_id'    => $wallet->id,
            'name'                => $request->name,
            'amount'              => $request->amount,
            'interest_rate'       => 5.00,
            'lock_duration_years' => $request->lock_duration_years,
            'locked_until'        => now()->addYears($request->lock_duration_years)->toDateString(),
            'status'              => 'active',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Savings locked successfully',
            'data'    => [
                'locked_saving'   => $lockedSaving,
                'locked_until'    => $lockedSaving->locked_until->format('d M Y'),
                'maturity_amount' => $lockedSaving->maturity_amount,
                'new_balance'     => $wallet->fresh()->balance,
            ],
        ], 201);
    }

    // GET /api/locked-savings/{id}
    public function show($id): JsonResponse
    {
        $saving = LockedSavings::where('user_id', Auth::id())->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => [
                ...$saving->toArray(),
                'has_matured'     => $saving->hasMatured(),
                'interest_earned' => $saving->interest_earned,
                'maturity_amount' => $saving->maturity_amount,
            ],
        ]);
    }

    // POST /api/locked-savings/{id}/withdraw
    public function withdraw($id): JsonResponse
    {
        $saving = LockedSavings::where('user_id', Auth::id())
            ->where('status', 'active')
            ->findOrFail($id);

        // Check if matured
        if (!$saving->hasMatured()) {
            return response()->json([
                'status'       => 'error',
                'message'      => 'Savings not yet matured. Matures on ' . $saving->locked_until->format('d M Y'),
                'locked_until' => $saving->locked_until,
            ], 422);
        }

        $wallet = SavingWallet::where('user_id', Auth::id())
            ->where('id', $saving->saving_wallet_id)
            ->first();

        if (!$wallet) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Wallet not found',
            ], 404);
        }

        // Add maturity amount back to wallet
        $wallet->increment('balance', $saving->maturity_amount);

        $saving->update([
            'status'       => 'withdrawn',
            'withdrawn_at' => now(),
        ]);

        return response()->json([
            'status'          => 'success',
            'message'         => 'Locked savings withdrawn successfully',
            'amount_received' => $saving->maturity_amount,
            'new_balance'     => $wallet->fresh()->balance,
        ]);
    }
}
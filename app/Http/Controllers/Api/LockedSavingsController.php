<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LockedSavings;
use App\Models\SavingWallet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LockedSavingsController extends Controller
{
    // GET /api/locked-savings
    public function index(): JsonResponse
    {
        $savings = LockedSavings::where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(function ($saving) {
                return [
                    'id'                  => $saving->id,
                    'name'                => $saving->name,
                    'amount'              => $saving->amount,
                    'interest_rate'       => $saving->interest_rate,
                    'lock_duration_years' => $saving->lock_duration_years,
                    'locked_until'        => $saving->locked_until?->format('Y-m-d'),
                    'status'              => $saving->status,
                    'has_matured'         => $saving->hasMatured(),
                    'interest_earned'     => $saving->interest_earned,
                    'maturity_amount'     => $saving->maturity_amount,
                ];
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
            'name'             => 'required|string|max:255',
            'amount'           => 'required|numeric|min:1000',
            // FIX: accept duration_months from Flutter OR lock_duration_years
            'duration_months'  => 'nullable|integer|min:1',
            'lock_duration_years' => 'nullable|integer|min:1|max:5',
        ]);

        // FIX: was requiring saving_wallet_id from Flutter (which sent 0)
        // Now auto-get or create the wallet for this user
        $wallet = SavingWallet::firstOrCreate(
            ['user_id' => Auth::id()],
            ['name' => 'My Savings Wallet', 'balance' => 0]
        );

        if ($wallet->balance < $request->amount) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Insufficient wallet balance',
            ], 422);
        }

        // FIX: Flutter sends duration_months, convert to years for storage
        $durationMonths = $request->duration_months
            ?? ($request->lock_duration_years * 12)
            ?? 12;
        $durationYears = max(1, (int) ceil($durationMonths / 12));

        try {
            DB::beginTransaction();

            $wallet->decrement('balance', $request->amount);

            $lockedSaving = LockedSavings::create([
                'user_id'             => Auth::id(),
                'saving_wallet_id'    => $wallet->id,
                'name'                => $request->name,
                'amount'              => $request->amount,
                'interest_rate'       => 5.00,
                'lock_duration_years' => $durationYears,
                'locked_until'        => now()->addMonths($durationMonths),
                'status'              => 'active',
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Savings locked successfully',
                'data'    => [
                    'id'              => $lockedSaving->id,
                    'name'            => $lockedSaving->name,
                    'amount'          => $lockedSaving->amount,
                    'locked_until'    => $lockedSaving->locked_until?->format('d M Y'),
                    'maturity_amount' => $lockedSaving->maturity_amount,
                    'new_balance'     => $wallet->fresh()->balance,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/locked-savings/{id}
    public function show($id): JsonResponse
    {
        $saving = LockedSavings::where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'              => $saving->id,
                'name'            => $saving->name,
                'amount'          => $saving->amount,
                'interest_rate'   => $saving->interest_rate,
                'locked_until'    => $saving->locked_until?->format('Y-m-d'),
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

        if (!$saving->hasMatured()) {
            return response()->json([
                'status'       => 'error',
                'message'      => 'Savings not yet matured',
                'locked_until' => $saving->locked_until?->format('d M Y'),
            ], 422);
        }

        // FIX: was ->first() which could return null — use firstOrCreate
        $wallet = SavingWallet::firstOrCreate(
            ['user_id' => Auth::id()],
            ['name' => 'My Savings Wallet', 'balance' => 0]
        );

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
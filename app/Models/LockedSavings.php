<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LockedSavings;
use App\Models\SavingWallet;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LockedSavingsController extends Controller
{
    public function index(): JsonResponse
    {
        $savings = LockedSavings::where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(fn($s) => [
                'id'              => $s->id,
                'name'            => $s->name,
                'amount'          => $s->amount,
                'interest_rate'   => $s->interest_rate,
                'duration_months' => $s->lock_duration_years * 12,
                'maturity_date'   => $s->locked_until?->format('d M Y'),
                'status'          => $s->status,
                'has_matured'     => $s->hasMatured(),
                'interest_earned' => $s->interest_earned,
                'maturity_amount' => $s->maturity_amount,
                'created_at'      => (string) $s->created_at,
            ]);

        return response()->json(['status' => 'success', 'data' => $savings]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'amount'          => 'required|numeric|min:1000',
            'duration_months' => 'required|integer|min:1',
        ]);

        $durationMonths = (int) $request->duration_months;
        $durationYears  = max(1, (int) ceil($durationMonths / 12));

        $wallet = SavingWallet::firstOrCreate(
            ['user_id' => Auth::id()],
            ['name' => 'My Savings Wallet', 'balance' => 0]
        );

        try {
            DB::beginTransaction();

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

            Notification::notify(
                Auth::id(),
                '🔒 Savings Locked',
                'UGX ' . number_format($request->amount) . ' locked in "' . $request->name . '" for ' . $durationMonths . ' month(s). Matures on ' . $lockedSaving->locked_until->format('d M Y') . '.',
                'savings_update'
            );

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Savings locked successfully',
                'data'    => [
                    'id'              => $lockedSaving->id,
                    'name'            => $lockedSaving->name,
                    'amount'          => $lockedSaving->amount,
                    'maturity_date'   => $lockedSaving->locked_until?->format('d M Y'),
                    'maturity_amount' => $lockedSaving->maturity_amount,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        $saving = LockedSavings::where('user_id', Auth::id())->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'              => $saving->id,
                'name'            => $saving->name,
                'amount'          => $saving->amount,
                'interest_rate'   => $saving->interest_rate,
                'maturity_date'   => $saving->locked_until?->format('d M Y'),
                'has_matured'     => $saving->hasMatured(),
                'interest_earned' => $saving->interest_earned,
                'maturity_amount' => $saving->maturity_amount,
            ],
        ]);
    }

    public function withdraw($id): JsonResponse
    {
        $saving = LockedSavings::where('user_id', Auth::id())
            ->where('status', 'active')
            ->findOrFail($id);

        if (!$saving->hasMatured()) {
            return response()->json([
                'status'       => 'error',
                'message'      => 'Savings not yet matured. Locked until ' . $saving->locked_until?->format('d M Y'),
                'locked_until' => $saving->locked_until?->format('d M Y'),
            ], 422);
        }

        $saving->update(['status' => 'withdrawn', 'withdrawn_at' => now()]);

        Notification::notify(
            Auth::id(),
            '🔓 Locked Savings Withdrawn',
            'UGX ' . number_format($saving->maturity_amount) . ' (including interest) withdrawn from "' . $saving->name . '".',
            'savings_update'
        );

        return response()->json([
            'status'          => 'success',
            'message'         => 'Withdrawn successfully',
            'amount_received' => $saving->maturity_amount,
        ]);
    }
}
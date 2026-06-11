<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LockedSavings;
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
                    'duration_months'     => $saving->lock_duration_years * 12,
                    'maturity_date'       => $saving->locked_until?->format('d M Y'),
                    'status'              => $saving->status,
                    'has_matured'         => $saving->hasMatured(),
                    'interest_earned'     => $saving->interest_earned,
                    'maturity_amount'     => $saving->maturity_amount,
                    'created_at'          => (string) $saving->created_at,
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
            'name'            => 'required|string|max:255',
            'amount'          => 'required|numeric|min:1000',
            'duration_months' => 'required|integer|min:1',
        ]);

        $durationMonths = (int) $request->duration_months;
        $durationYears  = max(1, (int) ceil($durationMonths / 12));

        try {
            DB::beginTransaction();

            $lockedSaving = LockedSavings::create([
                'user_id'             => Auth::id(),
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
                    'maturity_date'   => $lockedSaving->locked_until?->format('d M Y'),
                    'maturity_amount' => $lockedSaving->maturity_amount,
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
                'maturity_date'   => $saving->locked_until?->format('d M Y'),
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
                'message'      => 'Savings not yet matured. Locked until ' . $saving->locked_until?->format('d M Y'),
                'locked_until' => $saving->locked_until?->format('d M Y'),
            ], 422);
        }

        $saving->update([
            'status'       => 'withdrawn',
            'withdrawn_at' => now(),
        ]);

        return response()->json([
            'status'          => 'success',
            'message'         => 'Locked savings withdrawn successfully',
            'amount_received' => $saving->maturity_amount,
        ]);
    }
}
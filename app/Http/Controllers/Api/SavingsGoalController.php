<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavingsGoal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SavingsGoalController extends Controller
{
    // GET /api/savings-goals
    public function index(): JsonResponse
    {
        $goals = SavingsGoal::where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(function ($goal) {
                $goal->progress_percentage = $goal->progress_percentage;
                return $goal;
            });

        return response()->json([
            'status' => 'success',
            'data'   => $goals,
        ]);
    }

    // GET /api/savings-goals/{id}
    public function show($id): JsonResponse
    {
        $goal = SavingsGoal::where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => [
                ...$goal->toArray(),
                'progress_percentage' => $goal->progress_percentage,
            ],
        ]);
    }

    // POST /api/savings-goals
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'savings_wallet_id' => 'required|exists:savings_wallets,id',
            'name'              => 'required|string|max:255',
            'target_amount'     => 'required|numeric|min:1',
            'target_date'       => 'nullable|date|after:today',
        ]);

        $goal = SavingsGoal::create([
            ...$validated,
            'user_id'        => Auth::id(),
            'current_amount' => 0,
            'status'         => 'active',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Savings goal created successfully',
            'data'    => $goal,
        ], 201);
    }

    // PUT /api/savings-goals/{id}
    public function update(Request $request, $id): JsonResponse
    {
        $goal = SavingsGoal::where('user_id', Auth::id())
            ->findOrFail($id);

        $validated = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'target_amount' => 'sometimes|numeric|min:1',
            'target_date'   => 'nullable|date|after:today',
            'status'        => 'sometimes|in:active,completed,cancelled',
        ]);

        $goal->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Savings goal updated successfully',
            'data'    => $goal,
        ]);
    }

    // DELETE /api/savings-goals/{id}
    public function destroy($id): JsonResponse
    {
        $goal = SavingsGoal::where('user_id', Auth::id())
            ->findOrFail($id);

        $goal->update(['status' => 'cancelled']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Savings goal cancelled successfully',
        ]);
    }
}
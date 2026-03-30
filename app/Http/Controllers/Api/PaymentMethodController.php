<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PaymentMethodController extends Controller
{
    // GET /api/payment-methods
    public function index(): JsonResponse
    {
        $methods = PaymentMethod::where('user_id', Auth::id())->get();

        return response()->json([
            'status' => 'success',
            'data'   => $methods,
        ]);
    }

    // POST /api/payment-methods
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type'           => 'required|in:mobile_money,bank_transfer',
            'provider'       => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_name'   => 'required|string|max:255',
            'is_default'     => 'nullable|boolean',
        ]);

        // If setting as default, unset others
        if ($request->is_default) {
            PaymentMethod::where('user_id', Auth::id())
                ->update(['is_default' => false]);
        }

        $method = PaymentMethod::create([
            ...$request->only(['type', 'provider', 'account_number', 'account_name']),
            'user_id'    => Auth::id(),
            'is_default' => $request->is_default ?? false,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Payment method added successfully',
            'data'    => $method,
        ], 201);
    }

    // PUT /api/payment-methods/{id}/set-default
    public function setDefault($id): JsonResponse
    {
        PaymentMethod::where('user_id', Auth::id())
            ->update(['is_default' => false]);

        $method = PaymentMethod::where('user_id', Auth::id())->findOrFail($id);
        $method->update(['is_default' => true]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Default payment method updated',
            'data'    => $method,
        ]);
    }

    // DELETE /api/payment-methods/{id}
    public function destroy($id): JsonResponse
    {
        $method = PaymentMethod::where('user_id', Auth::id())->findOrFail($id);
        $method->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Payment method removed',
        ]);
    }
}
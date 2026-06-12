<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    /**
     * POST /api/change-password
     * Requires: current_password, password, password_confirmation
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:6|confirmed|different:current_password',
        ]);

        $user = $request->user();

        // Verify the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
                'errors'  => [
                    'current_password' => ['Current password is incorrect.'],
                ],
            ], 401);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }
}
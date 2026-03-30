<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmailVerificationController extends Controller
{
    // GET /api/email/verify/{id}/{hash}
    // Verifies email using link from email
    public function verify(Request $request, $id, $hash): JsonResponse
    {
        $user = User::findOrFail($id);

        // Check if hash is valid
        if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid verification link',
            ], 403);
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Email already verified',
            ]);
        }

        // Mark email as verified
        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json([
            'status'  => 'success',
            'message' => 'Email verified successfully',
        ]);
    }

    // POST /api/email/resend
    // Resends verification email
    public function resend(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Email is already verified',
            ], 422);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'status'  => 'success',
            'message' => 'Verification email resent successfully',
        ]);
    }

    // GET /api/email/status
    // Check if logged in user's email is verified
    public function status(Request $request): JsonResponse
    {
        return response()->json([
            'status'         => 'success',
            'email_verified' => $request->user()->hasVerifiedEmail(),
            'verified_at'    => $request->user()->email_verified_at,
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // POST /api/register (registers as user by default)
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|unique:users,phone',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'role'     => 'user', // always user on register
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Account created successfully. Please check your email to verify your account.',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    // POST /api/admin/register (registers as admin - use secret key)
    public function registerAdmin(Request $request): JsonResponse
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'phone'        => 'required|string|unique:users,phone',
            'password'     => 'required|min:6|confirmed',
            'admin_secret' => 'required|string', // secret key to create admin
        ]);

        // Check admin secret key
        if ($request->admin_secret !== config('app.admin_secret', 'umoja@admin2024')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid admin secret key',
            ], 403);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'role'     => 'admin',
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Admin account created successfully.',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    // POST /api/login (works for both admin and user)
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid email or password',
            ], 401);
        }

        // Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Please verify your email before logging in.',
                'action'  => 'resend_verification',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Login successful',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role'  => $user->role,  // ← returns role so mobile app knows
            ],
            'token'   => $token,
        ]);
    }

    // POST /api/logout
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Logged out successfully',
        ]);
    }

    // GET /api/me
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'             => $request->user()->id,
                'name'           => $request->user()->name,
                'email'          => $request->user()->email,
                'phone'          => $request->user()->phone,
                'role'           => $request->user()->role,
                'email_verified' => $request->user()->hasVerifiedEmail(),
            ],
        ]);
    }
}
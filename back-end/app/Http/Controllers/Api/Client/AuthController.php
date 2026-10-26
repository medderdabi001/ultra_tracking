<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Authenticate Client User (Gérant / Assistant) and return Sanctum Token.
     */

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->where('status', 'active')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password, or account disabled.',
            ], 401);
        }

        // Check whether the user's company account is active
        $account = $user->account;

        if (!$account || $account->status !== 'active') {
            return response()->json([
                'message' => 'Your company account is disabled or inactive.',
            ], 403);
        }

        // Generate Sanctum Token only if both user and account are active
        $token = $user->createToken('mobile_app_token')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'account_id' => $user->account_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }


    /**
     * Get authenticated user profile.
     */
    public function profile(Request $request)
    {
        return response()->json($request->user()->load('account'));
    }
}

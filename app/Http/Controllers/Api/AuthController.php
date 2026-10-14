<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /** POST /api/login */
    public function login(Request $request)
    {
        // Validates the login data
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['sometimes', 'string', 'max:100'],
        ]);

        // Get the user with the first email that looks as shown
        $user = User::where('email', $data['email'])->first();

        // If no user under email, password provided does not match
        // 422 = Unprocessable content
        if (!$user || !Hash::check($data['password'], $user->password)) {
            // 422 (not 401) so the app shows this message instead of "please sign in".
            return response()->json(['message' => 'Invalid email or password.'], 422);
        }

        // Return the response as shown, token and user info
        return response()->json([
            'token' => $user->createToken($data['device_name'] ?? 'mobile')->plainTextToken,
            'user' => ['name' => $user->name, 'is_admin' => (bool) $user->role->role_name],
        ]);
    }

    /** POST /api/logout */
    public function logout(Request $request)
    {
        // Delete current access token
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Signed out.']);
    }
}

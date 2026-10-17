<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * POST /api/register
     *
     * Creates a new (non-admin) user account and returns a Sanctum token
     * in the same shape as /api/login, so the app can sign the user in
     * immediately after registration.
     */
    public function store(Request $request)
    {
        // Validate the signing up data
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:120'],
            'email'       => ['required', 'email', 'max:180', 'unique:users,email'],
            'password'    => ['required', 'confirmed', Password::min(8)
                                ->letters()
                                ->mixedCase()
                                ->numbers()],
            'device_name' => ['sometimes', 'string', 'max:100'],
            'role'        => ['required', 'integer', 'max:4']
        ]);

        // Create the user
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'active'   => 1,//By default you are active
            'role_id'  => $data['role']
        ]);

        // Fires e-mail verification notification if your User model uses
        // MustVerifyEmail — safe to leave even if you don't.
        event(new Registered($user));

        // Return a json of token and username
        return response()->json([
            'token' => $user->createToken($data['device_name'] ?? 'mobile')->plainTextToken,
            'user'  => ['name' => $user->name, 'is_admin' => $user->role->role_name=="System Admin" ? true : false],
        ], 201);
    }
}

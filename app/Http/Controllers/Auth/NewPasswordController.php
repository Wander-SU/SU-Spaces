<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        // Pre-fill token and email from the reset-link URL into the form.
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Validate the reset payload expected by Laravel's password broker.
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'token.required' => 'This field is required.',
            'email.required' => 'This field is required.',
            'password.required' => 'This field is required.',
            'password.confirmed' => 'Passwords do not match.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                // Save new password and rotate remember token to invalidate old sessions.
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                // Trigger Laravel's password-reset event hooks.
                event(new PasswordReset($user));
            }
        );

        // On success, send user back to login with framework status message.
        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        // On failure, keep email in form and show broker error.
        return back()->withInput($request->only('email'))->withErrors([
            'email' => __($status),
        ]);
    }
}

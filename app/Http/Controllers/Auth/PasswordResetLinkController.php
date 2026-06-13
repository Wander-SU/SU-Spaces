<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        // Shows the form where a user requests a password reset link.
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        // Basic validation before we attempt any password-reset action.
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'This field is required.',
        ]);

        $email = (string) $request->input('email');
        $userExists = User::query()->where('email', '=', $email)->exists();

        if ($userExists) {
            // Queue mail work after the response so the page returns quickly.
            dispatch(function () use ($email): void {
                Password::sendResetLink(['email' => $email]);
            })->afterResponse();
        }

        // Always return the same message to avoid exposing whether an email exists.
        return back()->with('status', 'If an account with that email exists, a reset link has been sent.');
    }
}

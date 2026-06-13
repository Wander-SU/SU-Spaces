<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Please enter your Username, Admission Number, or Employee ID.',
            'password.required' => 'Please enter your password.',
        ]);

        $identifier = trim($credentials['username']);
        $normalizedIdentifier = (string) Str::of($identifier)->replaceMatches('/\s+/', '')->lower();

        $user = User::query()
            ->whereRaw('LOWER(username) = ?', [$normalizedIdentifier], 'and')
            ->orWhereRaw('LOWER(admission_number) = ?', [Str::lower($identifier)])
            ->orWhereRaw('LOWER(employee_id) = ?', [Str::lower($identifier)])
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'username' => 'No account found. Please register first before logging in.',
            ]);
        }

        // Support both ban conventions used in the app/data: active=0 and banned/is_banned=1.
        $isBanned = (
            (int) ($user->active ?? 1) === 0
            || (int) ($user->banned ?? 0) === 1
            || (int) ($user->is_banned ?? 0) === 1
        );

        if ($isBanned) {
            throw ValidationException::withMessages([
                'username' => 'Your account has been banned and you can\'t log in.',
            ]);
        }

        if (! Auth::attempt(['email' => $user->email, 'password' => $credentials['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'password' => 'Incorrect password. Please try again.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('default'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

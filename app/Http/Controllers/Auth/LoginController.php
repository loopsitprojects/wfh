<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect(Auth::user()->dashboardRoute());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        // Find user case-insensitively
        $user = \App\Models\User::whereRaw('LOWER(username) = ?', [strtolower($credentials['username'])])->first();

        if ($user) {
            $authCredentials = [
                'username' => $user->username,
                'password' => $credentials['password'],
            ];

            if (Auth::attempt($authCredentials, $request->boolean('remember'))) {
                $request->session()->regenerate();

                if (!$user->is_active) {
                    Auth::logout();
                    return back()->withErrors(['username' => 'Your account has been deactivated. Contact your administrator.']);
                }

                return redirect($user->dashboardRoute());
            }
        }

        return back()->withErrors(['username' => 'Invalid username or password.'])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}

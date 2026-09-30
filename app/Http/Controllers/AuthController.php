<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VenueSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        $settings = VenueSetting::getSettings();
        return view('auth.login', compact('settings'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->isAdminAssistant() && !$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                return back()->withErrors([
                    'email' => 'Your admin assistant account has been deactivated. Please contact the court owner.',
                ])->onlyInput('email');
            }

            if ($user->isStaffOrAdmin()) {
                if ($user->isAdminAssistant()) {
                    return redirect()->intended(route($user->getFirstAllowedRoute()));
                }
                return redirect()->intended(route('owner.dashboard'));
            }

            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Demo login helper for fast testing
     */
    public function quickLogin(string $role)
    {
        $emailMap = [
            'owner' => 'owner@paddlefield.com',
            'admin' => 'admin@paddlefield.com',
            'platform' => 'platform@paddlefield.com',
            'platform_admin' => 'platform@paddlefield.com',
            'assistant' => 'assistant@paddlefield.com',
            'player' => 'player@gmail.com',
            'player2' => 'player2@gmail.com',
            'paolo' => 'paolo@gmail.com',
            'paolosoriano' => 'paolo@gmail.com',
        ];

        if (!isset($emailMap[$role])) {
            return redirect()->route('login');
        }

        $user = User::where('email', $emailMap[$role])->first();

        if ($user) {
            if ($user->isAdminAssistant() && !$user->is_active) {
                return redirect()->route('login')->with('error', 'Demo assistant account is currently inactive.');
            }

            Auth::login($user);
            request()->session()->regenerate();

            if ($user->isPlatformAdmin()) {
                if (\Illuminate\Support\Facades\Route::has('platform.venues')) {
                    return redirect()->route('platform.venues')->with('success', "Logged in as {$user->name}");
                }
                if (\Illuminate\Support\Facades\Route::has('platform.dashboard')) {
                    return redirect()->route('platform.dashboard')->with('success', "Logged in as {$user->name}");
                }
                return redirect()->route('owner.dashboard')->with('success', "Logged in as {$user->name}");
            }

            if ($user->isStaffOrAdmin()) {
                if ($user->isAdminAssistant()) {
                    return redirect()->route($user->getFirstAllowedRoute())->with('success', "Logged in as {$user->name}");
                }
                return redirect()->route('owner.dashboard')->with('success', "Logged in as {$user->name}");
            }

            return redirect()->route('home')->with('success', "Logged in as {$user->name}");
        }

        return redirect()->route('login')->with('error', 'Demo user not found.');
    }

    public function showRegister()
    {
        $settings = VenueSetting::getSettings();
        return view('auth.register', compact('settings'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:25',
            'password' => 'required|string|min:8|confirmed',
            'account_type' => 'nullable|string|in:client,court_owner',
        ]);

        $isOwner = ($request->input('account_type') === 'court_owner');
        $role = $isOwner ? 'court_owner' : 'client';

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => $role,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        if ($isOwner) {
            return redirect()->route('owner.onboard.step1')
                ->with('success', 'Account created! Welcome to the court owner portal. Let\'s set up your venue.');
        }

        return redirect()->route('home')->with('success', 'Registration successful! Welcome to the Pickleball Platform.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'You have been logged out.');
    }
}

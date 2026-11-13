<?php

namespace App\Http\Controllers;

use App\Services\SandboxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function __construct(private SandboxService $sandbox) {}

    public function index()
    {
        $settings = auth()->user()->getSettings();

        return view('settings.preferences', compact('settings'));
    }

    /** Profile & identity management view */
    public function profile()
    {
        $user = auth()->user();

        return view('settings.profile', compact('user'));
    }

    /** Security & authentication credential view */
    public function security()
    {
        $user = auth()->user();

        return view('settings.security', compact('user'));
    }

    /** About & privacy architecture view */
    public function about()
    {
        $user = auth()->user();
        $settings = $user->getSettings();

        return view('settings.about', compact('user', 'settings'));
    }

    /** Save preferences: base currency + alert toggles */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'base_currency' => 'required|string|size:3',
            'alert_7_days' => 'nullable|boolean',
            'alert_3_days' => 'nullable|boolean',
        ]);

        $user = auth()->user();

        if ($user->isSandbox()) {
            $this->sandbox->updatePreferences($user->id, $validated['base_currency']);

            return back()->with('success', 'Currency preference updated for this sandbox session (email alerts remain disabled in sandbox mode).');
        }

        $settings = $user->getSettings();
        $settings->update([
            'base_currency' => strtoupper($validated['base_currency']),
            'alert_7_days' => $request->boolean('alert_7_days'),
            'alert_3_days' => $request->boolean('alert_3_days'),
        ]);

        return back()->with('success', 'Preferences saved.');
    }

    /** Save dark mode preference from server (also backed by localStorage) */
    public function updateDarkMode(Request $request)
    {
        $user = auth()->user();

        if ($user->isSandbox()) {
            $darkMode = $this->sandbox->updateDarkMode($user->id, $request->boolean('dark_mode'));

            return response()->json(['dark_mode' => $darkMode]);
        }

        $settings = $user->getSettings();
        $settings->update(['dark_mode' => $request->boolean('dark_mode')]);

        return response()->json(['dark_mode' => $settings->dark_mode]);
    }

    /** Update account name + email */
    public function updateAccount(Request $request)
    {
        $user = auth()->user();

        if ($user->isSandbox()) {
            return back()->with('error', 'The demo evaluation account email cannot be modified.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
        ]);

        $user->fill($validated);

        $emailChanged = $user->isDirty('email');

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();

            return redirect()->route('verification.notice')
                ->with('status', 'verification-link-sent');
        }

        return back()->with('success', 'Account details updated.');
    }

    /** Change password */
    public function updatePassword(Request $request)
    {
        if (auth()->user()->isSandbox()) {
            return back()->with('error', 'The demo evaluation account password cannot be changed.');
        }

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    /** Permanently delete user account and erase all associated data */
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        if ($user->isSandbox()) {
            return back()->with('error', 'The demo evaluation account cannot be deleted.');
        }

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        Auth::logout();

        DB::transaction(function () use ($user) {
            $user->subscriptions()->delete();
            $user->categories()->delete();
            $user->settings()->delete();
            $user->delete();
        });

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'Your account and all associated data have been permanently deleted.');
    }
}

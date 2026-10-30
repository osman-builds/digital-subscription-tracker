<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = auth()->user()->getSettings();

        return view('settings.index', compact('settings'));
    }

    /** Save preferences: base currency + alert toggles */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'base_currency' => 'required|string|size:3',
            'alert_7_days' => 'nullable|boolean',
            'alert_3_days' => 'nullable|boolean',
        ]);

        $settings = auth()->user()->getSettings();
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
        $settings = auth()->user()->getSettings();
        $settings->update(['dark_mode' => $request->boolean('dark_mode')]);

        return response()->json(['dark_mode' => $settings->dark_mode]);
    }

    /** Update account name + email */
    public function updateAccount(Request $request)
    {
        $user = auth()->user();

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
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }
}

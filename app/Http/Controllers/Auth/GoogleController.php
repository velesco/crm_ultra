<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user already exists
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Update user info if they signed up with Google
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => now(), // Auto-verify email from Google
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(24)), // Random password since they'll use Google
                ]);

                // Assign default role if using Spatie Permissions
                if (method_exists($user, 'assignRole')) {
                    $user->assignRole('user'); // or whatever your default role is
                }
            }

            // Log the user in
            Auth::login($user, true);

            return redirect()->intended(route('dashboard'))->with('success', 'Successfully logged in with Google!');

        } catch (\Exception $e) {
            \Log::error('Google login error: '.$e->getMessage());

            return redirect()->route('login')->with('error', 'Unable to login with Google. Please try again or use email/password.');
        }
    }
}

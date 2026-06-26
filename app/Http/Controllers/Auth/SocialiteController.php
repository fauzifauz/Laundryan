<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\GoogleWelcomeMail;
use App\Mail\GoogleLinkedMail;
use App\Mail\AdminNewUserNotificationMail;
use App\Mail\NewDeviceAlertMail;
use App\Mail\AccountPendingReminderMail;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialiteController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::where('google_id', $googleUser->getId())
                        ->orWhere('email', $googleUser->getEmail())
                        ->first();

            if ($user) {
                // If user exists but google_id was empty, it means they are linking for the first time
                $isLinking = empty($user->google_id);

                $user->update([
                    'google_id' => $googleUser->getId(),
                    'google_token' => $googleUser->token,
                ]);

                if ($isLinking) {
                    Mail::to($user->email)->send(new GoogleLinkedMail($user));
                }
            } else {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'google_token' => $googleUser->token,
                    'password' => null,
                    'role' => 'pelanggan',
                    'status' => 'active',
                ]);

                // Send Welcome Email
                Mail::to($user->email)->send(new GoogleWelcomeMail($user));

                // Notify Admin
                $adminEmail = env('ADMIN_EMAIL', 'ithelpsdesk1@gmail.com');
                Mail::to($adminEmail)->send(new AdminNewUserNotificationMail($user, 'Google'));
            }
            if ($user->status !== 'active') {
                Mail::to($user->email)->send(new AccountPendingReminderMail($user));
                return redirect()->route('login')->with('error', 'Your account is inactive. Please contact admin.');
            }

            // New Device / New IP Detection
            $currentUserAgent = request()->userAgent();
            $currentIp        = request()->ip();

            $agentChanged = $user->last_user_agent && $user->last_user_agent !== $currentUserAgent;
            $ipChanged    = $user->last_login_ip   && $user->last_login_ip   !== $currentIp;

            if ($agentChanged || $ipChanged) {
                Mail::to($user->email)->send(new NewDeviceAlertMail($user, $currentUserAgent, $currentIp));
            }

            $user->update([
                'last_user_agent' => $currentUserAgent,
                'last_login_ip'   => $currentIp,
            ]);

            Auth::login($user);

            \App\Models\ActivityLog::log('Auth & Security', 'Login via Google', 'User "' . $user->name . '" logged in via Google', 'Auth', null, null, null, $user);

            if ($agentChanged || $ipChanged) {
                \App\Models\ActivityLog::log('Auth & Security', 'Login from New Device', 'Login detected from new device (' . \App\Models\ActivityLog::parseBrowser($currentUserAgent) . ' / ' . \App\Models\ActivityLog::parseDevice($currentUserAgent) . ')', 'Auth', null, null, null, $user);
            }

            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard', absolute: false));
            } elseif ($user->role === 'karyawan') {
                return redirect()->intended(route('karyawan.dashboard', absolute: false));
            } elseif ($user->role === 'kurir') {
                return redirect()->intended(route('kurir.dashboard', absolute: false));
            }

            return redirect()->intended(route('dashboard', absolute: false));

        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Something went wrong with Google Login.');
        }
    }
}

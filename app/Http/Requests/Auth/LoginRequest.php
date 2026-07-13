<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewDeviceAlertMail;
use App\Mail\BruteForceAlertMail;
use App\Mail\AccountPendingReminderMail;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'captcha' => ['required', 'numeric'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $errors = [];

        // 1. Check Math Captcha
        if ($this->captcha != session('math_captcha_result')) {
            $errors['captcha'] = 'The math verification answer is incorrect. Please try again.';
        }

        // 2. Check if user exists
        $user = \App\Models\User::where('email', $this->email)->first();

        if (!$user) {
            RateLimiter::hit($this->throttleKey());
            $errors['email'] = 'The email address is not registered in our system.';
            \App\Models\ActivityLog::create([
                'user_id' => null,
                'user_name' => null,
                'email' => $this->email,
                'role' => 'sistem',
                'category' => 'Auth & Security',
                'activity_type' => 'Failed Login',
                'description' => 'Failed login attempt using email ' . $this->email,
                'module' => 'Auth',
                'ip_address' => $this->ip(),
                'browser' => \App\Models\ActivityLog::parseBrowser($this->userAgent()),
                'device' => \App\Models\ActivityLog::parseDevice($this->userAgent()),
                'user_agent' => $this->userAgent(),
            ]);
            throw ValidationException::withMessages($errors);
        }

        // Check if user is a Google-only user
        if (empty($user->password) && !empty($user->google_id)) {
            \App\Models\ActivityLog::create([
                'user_id' => null,
                'user_name' => null,
                'email' => $this->email,
                'role' => 'sistem',
                'category' => 'Auth & Security',
                'activity_type' => 'Failed Login',
                'description' => 'Failed login attempt using email ' . $this->email . ' (linked with Google)',
                'module' => 'Auth',
                'ip_address' => $this->ip(),
                'browser' => \App\Models\ActivityLog::parseBrowser($this->userAgent()),
                'device' => \App\Models\ActivityLog::parseDevice($this->userAgent()),
                'user_agent' => $this->userAgent(),
            ]);
            throw ValidationException::withMessages([
                'error' => 'This account is linked with Google. Please use the "Sign in with Google" button to login.',
            ]);
        }

        // 3. Check Password (if captcha was wrong, we still check password to show both errors)
        $passwordCorrect = Auth::validate($this->only('email', 'password'));

        if (!$passwordCorrect) {
            $errors['password'] = 'The password you entered is incorrect. Please try again.';
            \App\Models\ActivityLog::create([
                'user_id' => null,
                'user_name' => null,
                'email' => $this->email,
                'role' => 'sistem',
                'category' => 'Auth & Security',
                'activity_type' => 'Failed Login',
                'description' => 'Failed login attempt using email ' . $this->email,
                'module' => 'Auth',
                'ip_address' => $this->ip(),
                'browser' => \App\Models\ActivityLog::parseBrowser($this->userAgent()),
                'device' => \App\Models\ActivityLog::parseDevice($this->userAgent()),
                'user_agent' => $this->userAgent(),
            ]);
            
            // Get current lockout level
            $level = Cache::get('lockout_level_'.$this->throttleKey(), 0);
            
            if (RateLimiter::attempts($this->throttleKey()) >= 5) {
                $level++;
                Cache::put('lockout_level_'.$this->throttleKey(), $level, 3600);
            }

            $decays = [60, 300, 900, 1800, 3600];
            $decaySeconds = $decays[min($level, 4)];
            $attempts = RateLimiter::hit($this->throttleKey(), $decaySeconds);

            if ($attempts >= 5) {
                $lockoutMinutes = ceil($decaySeconds / 60);
                if ($attempts === 5 && !empty($user->password) && empty($user->google_id)) {
                    Mail::to($user->email)->send(new BruteForceAlertMail($user, $lockoutMinutes));
                }
                throw ValidationException::withMessages([
                    'error' => 'Too many login attempts. For security, your account is locked for ' . $lockoutMinutes . ' minutes.',
                ]);
            }
        }

        // If there are any errors (captcha, password, or both), throw them now
        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        // Finalize login
        Auth::login($user, $this->boolean('remember'));

        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();
            
            // Send a reminder email about pending approval
            Mail::to($user->email)->send(new AccountPendingReminderMail($user));

            throw ValidationException::withMessages([
                'error' => 'Your account is under admin review or is inactive. Please check your email for more info.',
            ]);
        }

        // New Device / New IP Detection
        $currentUserAgent = $this->userAgent();
        $currentIp        = $this->ip();

        $agentChanged = $user->last_user_agent && $user->last_user_agent !== $currentUserAgent;
        $ipChanged    = $user->last_login_ip   && $user->last_login_ip   !== $currentIp;

        if ($agentChanged || $ipChanged) {
            Mail::to($user->email)->send(new NewDeviceAlertMail($user, $currentUserAgent, $currentIp));
        }

        $user->update([
            'last_user_agent' => $currentUserAgent,
            'last_login_ip'   => $currentIp,
        ]);

        \App\Models\ActivityLog::log('Auth & Security', 'Successful Login', 'User "' . $user->name . '" logged in from IP ' . $currentIp, 'Auth', null, null, null, $user);

        if ($agentChanged || $ipChanged) {
            \App\Models\ActivityLog::log('Auth & Security', 'Login from New Device', 'Login detected from new device (' . \App\Models\ActivityLog::parseBrowser($currentUserAgent) . ' / ' . \App\Models\ActivityLog::parseDevice($currentUserAgent) . ')', 'Auth', null, null, null, $user);
        }

        RateLimiter::clear($this->throttleKey());
        Cache::forget('lockout_level_'.$this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        $message = trans('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => ceil($seconds / 60),
        ]);

        throw ValidationException::withMessages([
            'error' => $message,
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}

<?php

namespace App\Listeners;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ClearLoginLockout
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PasswordReset $event): void
    {
        $email = $event->user->email;
        $ip = request()->ip();
        $key = \Illuminate\Support\Str::transliterate(\Illuminate\Support\Str::lower($email).'|'.$ip);

        // Clear Rate Limiter
        \Illuminate\Support\Facades\RateLimiter::clear($key);

        // Clear Lockout Level Cache
        \Illuminate\Support\Facades\Cache::forget('lockout_level_'.$key);
    }
}

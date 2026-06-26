<x-mail::message>
# Security Alert: New Login Detected

Hello {{ $user->name }},

We detected a login to your **Laundryan** account from a location or device we don't recognize.

<x-mail::panel>
**Login Details:**

| Info | Value |
|------|-------|
| 🌐 IP Address | `{{ $ip }}` |
| 💻 Device/Browser | {{ $userAgent }} |
| 🕐 Time | {{ now()->setTimezone('Asia/Jakarta')->format('D, d M Y H:i:s') }} WIB |
</x-mail::panel>

**If this was you**, you can safely ignore this email.

**If you did NOT perform this login**, your account may be compromised. Please take action immediately:

<x-mail::button :url="route('password.request')" color="red">
🔒 Reset My Password Now
</x-mail::button>

We recommend you also review your account activity and enable a strong, unique password.

Thanks,
{{ config('app.name') }} Security Team
</x-mail::message>

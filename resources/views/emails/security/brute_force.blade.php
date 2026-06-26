<x-mail::message>
# Security Alert: Failed Login Attempts

Hello {{ $user->name }},

We noticed multiple failed login attempts on your Laundryan account. 

**For your security, your account has been temporarily locked for {{ $lockoutMinutes }} minute(s).** If these attempts continue, the lockout duration will increase to protect your data. 

If you are having trouble remembering your password, you can reset it using the link below.

If this wasn't you, someone may be trying to access your account. We recommend updating your password to a stronger one immediately.

<x-mail::button :url="route('password.request')">
Reset Password
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

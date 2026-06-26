<x-mail::message>
# Security Alert: Google Account Linked

Hello {{ $user->name }},

This is a security notification to inform you that your Laundryan account has been successfully linked with Google.

You can now use the **"Sign in with Google"** button to access your account.

If you did not perform this action, please contact our support team immediately.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

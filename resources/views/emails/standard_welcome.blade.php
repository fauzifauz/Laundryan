<x-mail::message>
# Welcome to Laundryan!

Hello {{ $user->name }},

Thank you for joining Laundryan! Your account has been successfully created and is now **active**.

You can now log in using your email address and the password you created during registration.

<x-mail::button :url="route('login')">
Login to Your Account
</x-mail::button>

If you have any questions or need assistance, feel free to contact our support team.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

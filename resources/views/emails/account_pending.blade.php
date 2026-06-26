<x-mail::message>
# Account Status Update

Hello {{ $user->name }},

We noticed you tried to log in to your Laundryan account. 

Currently, your account is still **pending administrator approval**. Our team is reviewing your registration and will notify you as soon as your account is activated.

Thank you for your patience. We look forward to serving you soon!

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

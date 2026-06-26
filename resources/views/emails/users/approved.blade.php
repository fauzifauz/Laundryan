<x-mail::message>
# Account Approved!

Hello, {{ $user->name }}.

Your account (Role: {{ ucfirst($user->role) }}) has been approved by the Administrator. 
You can now log in to the Laundryan portal.

<x-mail::button :url="route('login')">
Login Now
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

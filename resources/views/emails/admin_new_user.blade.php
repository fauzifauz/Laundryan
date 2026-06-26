<x-mail::message>
# New User Registration

Hello Administrator,

A new user has registered on the Laundryan platform.

**User Details:**
- **Name:** {{ $user->name }}
- **Email:** {{ $user->email }}
- **Method:** {{ $method }}
- **Status:** {{ $user->status == 'active' ? 'Already Active' : 'Pending Approval' }}

Please check the admin dashboard for more details.

<x-mail::button :url="route('admin.dashboard')">
View Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

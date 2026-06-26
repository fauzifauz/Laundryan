<x-mail::message>
# Welcome to Laundryan!

Hello {{ $user->name }},

Welcome to Laundryan! Your account has been successfully created and is now **active**. 

You can start placing your laundry orders right away through our portal.

<x-mail::button :url="config('app.url')">
Start Ordering Now
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

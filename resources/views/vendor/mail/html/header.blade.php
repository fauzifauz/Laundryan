@props(['url'])
@php
    $siteSetting = \App\Models\LandingPageSetting::where('key', 'site')->first();
    $logoUrl = $siteSetting?->content['logo_url'] ?? null;
    $siteName = $siteSetting?->content['name'] ?? config('app.name', 'LAUNDRYAN');
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (!empty($logoUrl))
<img src="{{ $logoUrl }}" class="logo" alt="{{ $siteName }}" style="max-height: 60px; width: auto;">
@else
<span style="font-size: 24px; font-weight: 900; color: #005bc0; letter-spacing: -1px; text-transform: uppercase; font-family: 'Plus Jakarta Sans', Arial, sans-serif;">{{ $siteName }}</span>
@endif
</a>
</td>
</tr>

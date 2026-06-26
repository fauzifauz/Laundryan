@php
    $siteSettings = \App\Models\LandingPageSetting::where('key', 'site')->first()?->content;
    $logoUrl = $siteSettings['logo_url'] ?? null;
    $siteName = $siteSettings['name'] ?? 'LAUNDRYAN';
@endphp

@if($logoUrl)
    <img src="{{ $logoUrl }}" alt="{{ $siteName }}" {{ $attributes->merge(['class' => 'h-9 w-auto object-contain']) }}>
@else
    <div {{ $attributes->merge(['class' => 'text-xl font-black tracking-tighter text-primary uppercase']) }}>
        {{ $siteName }}
    </div>
@endif

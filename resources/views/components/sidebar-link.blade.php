@props(['active', 'icon'])

@php
$classes = ($active ?? false)
            ? 'relative flex items-center gap-4 px-4 py-3.5 bg-white text-[#005bc0] rounded-2xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1)] font-bold transition-all duration-300 scale-[1.03] z-10'
            : 'relative flex items-center gap-4 px-4 py-3.5 text-white/70 hover:bg-white/10 hover:text-white rounded-2xl font-semibold transition-all duration-300 hover:translate-x-2 group';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if($active)
        <!-- Active Indicator Dot -->
        <div class="absolute -left-2 w-1.5 h-8 bg-white rounded-r-full shadow-[0_0_15px_rgba(255,255,255,0.8)]"></div>
    @endif

    @if($icon)
        <span class="material-symbols-outlined transition-transform duration-300 {{ $active ? 'text-[#005bc0] scale-110' : 'text-white/40 group-hover:text-white group-hover:scale-110' }}">
            {{ $icon }}
        </span>
    @endif
    
    <span class="text-sm tracking-tight">{{ $slot }}</span>
    
    @if(!$active)
        <!-- Hover Arrow Icon (Hidden by default) -->
        <span class="material-symbols-outlined ml-auto text-xs opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300">chevron_right</span>
    @endif
</a>

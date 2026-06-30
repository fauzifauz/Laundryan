{{-- Reusable courier card partial for the dashboard split columns.
     Variables expected:
       $history     - courier history array (id, name, phone, email, initial, role, orders)
       $accentClass - tailwind classes for the role badge (e.g. 'bg-blue-50 text-brand border-blue-100')
--}}
<div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 flex items-center justify-between hover:shadow-md transition-all duration-300 group">

    {{-- Clickable area: opens the courier detail modal --}}
    <div onclick="openCourierModal({{ json_encode($history) }})"
         class="flex items-center gap-4 cursor-pointer flex-1 text-left min-w-0">

        {{-- Avatar --}}
        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-brand to-blue-600 text-white
                    flex items-center justify-center font-black text-base shadow-sm shrink-0">
            {{ $history['initial'] }}
        </div>

        {{-- Info --}}
        <div class="min-w-0">
            <h4 class="text-sm font-bold text-gray-800 truncate">{{ $history['name'] }}</h4>
            <span class="inline-flex items-center px-2 py-0.5 text-[9px] font-black
                         border rounded-full uppercase tracking-wider mt-1 {{ $accentClass }}">
                {{ $history['role'] }}
            </span>
            <p class="text-[11px] text-gray-400 font-mono mt-1 truncate">{{ $history['phone'] }}</p>
        </div>
    </div>

    {{-- WhatsApp shortcut --}}
    <div class="shrink-0 ml-3">
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $history['phone']) }}"
           target="_blank"
           onclick="event.stopPropagation()"
           title="Contact via WhatsApp"
           class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center
                  hover:bg-emerald-100 transition-colors shadow-sm">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                <path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984a9.96 9.96 0 001.333 4.993L2 22l5.13-1.347a9.948 9.948 0 004.877 1.277h.005c5.505 0 9.989-4.478 9.99-9.985A9.97 9.97 0 0012.012 2zm6.069 13.985c-.25.702-1.246 1.285-1.71 1.342-.463.057-.927.278-3.003-.57a11.144 11.144 0 01-4.71-3.125 12.08 12.08 0 01-2.228-3.807c-.156-.475-.417-.79-.408-1.312.008-.521.217-.775.392-.953.175-.178.384-.263.576-.263.192 0 .384.004.549.012.176.009.349-.06.529.378.188.459.645 1.57.701 1.685.056.115.093.248.016.4-.076.152-.152.247-.29.414-.138.167-.296.347-.42.493-.138.156-.282.327-.122.602.16.275.711 1.17 1.523 1.89.963.854 1.867 1.13 2.143 1.268.275.137.435.114.596-.069.16-.183.69-.803.873-1.077.184-.275.367-.229.62-.137.253.091 1.6.753 1.875.891.275.137.458.206.52.312.062.106.062.612-.188 1.314z"/>
            </svg>
        </a>
    </div>
</div>

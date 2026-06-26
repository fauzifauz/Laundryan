@php
$sc = ['present'=>'bg-emerald-50 text-emerald-700 border-emerald-100','late'=>'bg-amber-50 text-amber-700 border-amber-100','absent'=>'bg-rose-50 text-rose-700 border-rose-100','permit'=>'bg-blue-50 text-blue-700 border-blue-100','leave'=>'bg-purple-50 text-purple-700 border-purple-100'];
$sl = ['present'=>'Present','late'=>'Late','absent'=>'Absent','permit'=>'Permit','leave'=>'Leave'];
$ac = ['pending'=>'bg-amber-100 text-amber-800 border-amber-200','approved'=>'bg-emerald-100 text-emerald-800 border-emerald-200','rejected'=>'bg-rose-100 text-rose-800 border-rose-200'];
$avatarBg = $record->user->role === 'kurir' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-blue-50 text-blue-600 border-blue-100';
@endphp
<tr class="hover:bg-gray-50/50 transition-colors">
    <td class="px-5 py-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl {{ $avatarBg }} flex items-center justify-center font-bold text-sm border flex-shrink-0">{{ substr($record->user->name,0,1) }}</div>
            <div class="min-w-0">
                <div class="font-bold text-gray-900 text-sm truncate">{{ $record->user->name }}</div>
                <div class="text-[9px] text-gray-400 font-bold uppercase truncate">{{ $record->user->email }}</div>
            </div>
        </div>
    </td>
    <td class="px-5 py-4 whitespace-nowrap">
        <div class="font-bold text-gray-800 text-xs">{{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}</div>
        <div class="text-[9px] text-gray-400">{{ \Carbon\Carbon::parse($record->date)->format('l') }}</div>
    </td>
    <td class="px-5 py-4 whitespace-nowrap">
        @if(in_array($record->status,['permit','leave']))
            <span class="text-gray-400 italic text-xs">Excused</span>
        @else
            <div class="flex items-center gap-1.5 text-xs font-black">
                <span class="text-blue-600">{{ $record->check_in ? \Carbon\Carbon::parse($record->check_in)->format('H:i') : '--:--' }}</span>
                <span class="text-gray-300">→</span>
                <span class="text-gray-700">{{ $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('H:i') : '--:--' }}</span>
            </div>
            @if($record->location_name)
            <div class="flex items-center gap-0.5 text-[9px] text-gray-400 mt-0.5 truncate max-w-[160px]" title="{{ $record->location_name }}">
                <span class="material-symbols-outlined text-[10px]">location_on</span>{{ Str::limit($record->location_name,22) }}
            </div>
            @endif
        @endif
    </td>
    <td class="px-5 py-4 text-center">
        <span class="px-2.5 py-0.5 text-[9px] font-black rounded-full border uppercase tracking-widest {{ $sc[$record->status] ?? 'bg-gray-50 text-gray-600 border-gray-100' }}">
            {{ $sl[$record->status] ?? ucfirst($record->status) }}
        </span>
    </td>
    <td class="px-5 py-4 text-center">
        @if($record->document_path)
            <button @click="openDetail({{ json_encode($record) }})" class="inline-flex items-center gap-1 text-[10px] font-black uppercase text-blue-600 hover:text-blue-800">
                <span class="material-symbols-outlined text-[14px]">description</span>Doc
            </button>
        @elseif($record->photo_path)
            <button @click="openDetail({{ json_encode($record) }})" class="group relative inline-block">
                <img src="{{ asset('storage/'.$record->photo_path) }}" class="w-9 h-9 rounded-xl object-cover border border-gray-100 shadow-sm group-hover:scale-105 transition-all">
            </button>
        @else
            <span class="text-gray-300 text-xs">—</span>
        @endif
    </td>
    <td class="px-5 py-4 text-center whitespace-nowrap">
        @if(in_array($record->status,['permit','leave']))
            <span class="px-2.5 py-0.5 text-[9px] font-black rounded-full border uppercase tracking-widest {{ $ac[$record->approval_status] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                {{ $record->approval_status }}
            </span>
        @else
            <span class="text-gray-300">—</span>
        @endif
    </td>
    <td class="px-5 py-4 text-right whitespace-nowrap">
        <div class="flex items-center justify-end gap-1.5">
            @if(in_array($record->status,['permit','leave']) && $record->approval_status==='pending')
                <form action="{{ route('admin.attendance.approve',$record->id) }}" method="POST" class="inline">
                    @csrf<button type="submit" title="Approve" class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-500 text-emerald-600 hover:text-white flex items-center justify-center transition-all">
                        <span class="material-symbols-outlined text-[15px]">done</span>
                    </button>
                </form>
                <button @click="openDetail({{ json_encode($record) }});showRejectForm=true" title="Reject" class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white flex items-center justify-center transition-all">
                    <span class="material-symbols-outlined text-[15px]">close</span>
                </button>
            @endif
            <button @click="openDetail({{ json_encode($record) }})" class="px-3 py-1.5 bg-gray-50 border border-gray-100 text-gray-600 text-[10px] font-black uppercase rounded-lg hover:bg-blue-50 hover:text-blue-600 hover:border-blue-100 transition-all">Details</button>
        </div>
    </td>
</tr>

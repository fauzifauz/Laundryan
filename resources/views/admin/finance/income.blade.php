<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                Income History
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.finance.index') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-gray-50 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">dashboard</span> Back to Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ 
        showModal: false, 
        imageUrl: null,
        receiptModal: false,
        receiptUrl: null,
        receiptType: null,
        downloadLoading: false,
        editModal: false,
        editImageUrl: null,
        editData: { id: null, date: '', category: '', amount: 0, payment_method: 'CASH', description: '', attachment_url: null },
        fileChosen(event) {
            const file = event.target.files[0];
            if (file) {
                this.imageUrl = URL.createObjectURL(file);
            }
        },
        viewReceipt(url) {
            this.receiptUrl = url;
            const ext = url.split('.').pop().toLowerCase();
            this.receiptType = (ext === 'pdf') ? 'pdf' : 'image';
            this.receiptModal = true;
        },
        openEditModal(item) {
            this.editData = {
                id: item.id,
                date: item.date,
                category: item.category,
                amount: item.amount,
                payment_method: item.payment_method,
                description: item.description || '',
                attachment_url: item.attachment ? '{{ Storage::url("") }}' + '/' + item.attachment : null
            };
            this.editImageUrl = this.editData.attachment_url;
            this.editModal = true;
        },
        editFileChosen(event) {
            const file = event.target.files[0];
            if (file) {
                this.editImageUrl = URL.createObjectURL(file);
            }
        },
        showToast: {{ (session('success') || session('warning')) ? 'true' : 'false' }},
        toastType: '{{ session('warning') ? "warning" : "success" }}',
        toastTitle: '{{ session('toast_title') ?: (session('new_income_created') ? "Income Recorded" : (session('income_updated') ? "Income Updated" : (session('income_deleted') ? "Income Deleted" : (session('success') ? "Success" : "Warning")))) }}',
        toastMessage: '{{ session('new_income_created') ? "New income record has been recorded successfully." : (session('income_updated') ? "Income record has been updated successfully." : (session('income_deleted') ? "Income record has been deleted successfully." : (session('success') ?: session('warning', '')))) }}',
        showConfirmModal: false,
        confirmTitle: '',
        confirmMessage: '',
        confirmActionUrl: '',
        confirmActionMethod: 'POST',
        confirmButtonText: 'Confirm',
        confirmButtonColorClass: 'bg-rose-600 hover:bg-rose-700'
    }"
    x-init="
        if (showToast) {
            setTimeout(() => { showToast = false; }, 5000);
        }
    ">
        <!-- Toast Alert Notification -->
        <div x-show="showToast" 
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            :class="{
                'bg-emerald-50 border-emerald-200 text-emerald-800': toastType === 'success',
                'bg-amber-50 border-amber-200 text-amber-800': toastType === 'warning'
            }"
            class="fixed top-6 right-6 z-[110] max-w-sm w-full border rounded-3xl p-5 shadow-2xl flex items-center justify-between overflow-hidden" 
            style="display: none;"
            x-cloak>
            <div :class="{
                'bg-emerald-600/10': toastType === 'success',
                'bg-amber-600/10': toastType === 'warning'
            }" class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div :class="{
                    'bg-emerald-100/50 border-emerald-200': toastType === 'success',
                    'bg-amber-100/50 border-amber-200': toastType === 'warning'
                }" class="w-10 h-10 rounded-2xl border flex items-center justify-center shadow-inner">
                    <span class="material-symbols-outlined text-xl" x-text="toastType === 'success' ? 'check_circle' : 'warning'" :class="{
                        'text-emerald-600': toastType === 'success',
                        'text-amber-600': toastType === 'warning'
                    }"></span>
                </div>
                <div>
                    <h4 class="font-black text-xs uppercase tracking-wider" x-text="toastTitle"></h4>
                    <p class="text-[11px] font-medium mt-0.5" :class="{
                        'text-emerald-700': toastType === 'success',
                        'text-amber-700': toastType === 'warning'
                    }" x-text="toastMessage"></p>
                </div>
            </div>
            <button @click="showToast = false" :class="{
                'text-emerald-600/60 hover:text-emerald-800 hover:bg-emerald-100/50': toastType === 'success',
                'text-amber-600/60 hover:text-amber-800 hover:bg-amber-100/50': toastType === 'warning'
            }" class="transition-colors p-2 rounded-xl relative z-10">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <!-- Confirmation Modal -->
        <div x-show="showConfirmModal" 
             class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-900/65 backdrop-blur-xs"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;"
             x-cloak>
             
            <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-gray-100/50 overflow-hidden"
                 @click.away="showConfirmModal = false"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4">
                 
                <!-- Header -->
                <div class="p-6 border-b border-gray-100 flex items-center gap-3 bg-gradient-to-r from-gray-50 to-white">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-xs">
                        <span class="material-symbols-outlined text-[20px]">help_center</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-gray-900" x-text="confirmTitle">Confirm Action</h3>
                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Please review the details below</p>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <p class="text-xs text-gray-650 font-bold leading-relaxed" x-text="confirmMessage"></p>
                </div>

                <!-- Footer -->
                <div class="p-6 border-t border-gray-100 flex justify-end gap-3 bg-gray-50/50">
                    <button @click="showConfirmModal = false" type="button"
                            class="py-2.5 px-5 bg-gray-150 hover:bg-gray-205 text-gray-700 text-[10px] font-black rounded-xl uppercase tracking-widest transition-all">
                        Cancel
                    </button>
                    <form :action="confirmActionUrl" method="POST">
                        @csrf
                        <input type="hidden" name="_method" :value="confirmActionMethod">
                        <button type="submit" 
                                :class="confirmButtonColorClass + ' py-2.5 px-5 text-white text-[10px] font-black rounded-xl uppercase tracking-widest shadow-md transition-all active:scale-95 border border-black/5'">
                            <span x-text="confirmButtonText">Confirm</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-3xl overflow-hidden border border-gray-100">
                <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50 flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">All Income Records</h3>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">
                            Period: <span class="text-emerald-600 font-black">{{ $periodLabel }}</span>
                            &nbsp;·&nbsp; Total: <span class="text-emerald-600 font-black">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
                        </p>
                    </div>

                    <!-- Filters -->
                    <div class="flex flex-wrap items-center gap-2">

                        <!-- Hidden auto-submit form -->
                        <form id="incomeFilterForm" action="{{ route('admin.finance.income') }}" method="GET">
                            @foreach(request()->except(['month','year','start_date','end_date']) as $k => $v)
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                            @endforeach
                            <input type="hidden" name="month" id="incomeHiddenMonth" value="{{ $filterMonth }}">
                            <input type="hidden" name="year"  id="incomeHiddenYear"  value="{{ $filterYear }}">
                        </form>

                        <!-- Month Filter -->
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-2.5 top-2 text-gray-400 text-[17px]">calendar_month</span>
                            <select onchange="document.getElementById('incomeHiddenMonth').value=this.value; document.getElementById('incomeFilterForm').submit();"
                                class="text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-9 pr-8 py-2 focus:outline-none appearance-none cursor-pointer shadow-sm">
                                <option value="all" {{ $filterMonth === 'all' ? 'selected' : '' }}>All Months</option>
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $filterMonth == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(2026, $m)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                        </div>

                        <!-- Year Filter -->
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-2.5 top-2 text-gray-400 text-[17px]">event</span>
                            <select onchange="document.getElementById('incomeHiddenYear').value=this.value; document.getElementById('incomeFilterForm').submit();"
                                class="text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-9 pr-8 py-2 focus:outline-none appearance-none cursor-pointer shadow-sm">
                                <option value="all" {{ $filterYear === 'all' ? 'selected' : '' }}>All Years</option>
                                @foreach(range(now()->year - 2, now()->year + 1) as $y)
                                    <option value="{{ $y }}" {{ $filterYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                            <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                        </div>

                        <!-- Custom Date Range -->
                        <div class="relative inline-block text-left" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="flex items-center justify-center w-9 h-9 bg-white border {{ $startDate ? 'border-emerald-500 text-emerald-600' : 'border-gray-200 text-gray-500' }} rounded-xl hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm group"
                                title="Custom Date Range">
                                <span class="material-symbols-outlined text-[18px] transition-transform group-hover:scale-110">date_range</span>
                            </button>
                            <div x-show="open" @click.away="open = false" style="display: none;"
                                class="absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-2xl border border-gray-100 z-50 p-4">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-3">Custom Date Range</p>
                                <form action="{{ route('admin.finance.income') }}" method="GET" class="space-y-3">
                                    @foreach(request()->except(['start_date','end_date','month','year']) as $k => $v)
                                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                    @endforeach
                                    <div>
                                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Start Date</label>
                                        <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="w-full rounded-lg border-gray-200 text-xs focus:ring-emerald-500 focus:border-emerald-500 font-medium text-gray-700 shadow-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">End Date</label>
                                        <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="w-full rounded-lg border-gray-200 text-xs focus:ring-emerald-500 focus:border-emerald-500 font-medium text-gray-700 shadow-sm" required>
                                    </div>
                                    <div class="flex gap-2 pt-1">
                                        <a href="{{ route('admin.finance.income') }}" class="flex-1 bg-gray-50 text-gray-500 font-black py-2 rounded-lg hover:bg-gray-100 transition-all text-[9px] uppercase tracking-widest text-center">Reset</a>
                                        <button type="submit" class="flex-1 bg-emerald-600 text-white font-black py-2 rounded-lg hover:bg-emerald-700 transition-all text-[9px] uppercase tracking-widest">Apply</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Clear filter -->
                        @if($filterMonth !== 'all' || $filterYear !== 'all' || $startDate || isset($filterService))
                            <a href="{{ route('admin.finance.income') }}"
                                class="flex items-center gap-1 text-[10px] font-black text-rose-500 hover:text-rose-700 uppercase tracking-widest transition-colors">
                                <span class="material-symbols-outlined text-[14px]">close</span> Clear
                            </a>
                        @endif

                        <button @click="showModal = true" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100 flex items-center gap-2 active:scale-95">
                            <span class="material-symbols-outlined text-[18px]">add</span> Log Income
                        </button>
                    </div>
                </div>

                @if(isset($filterService) && $filterService)
                    <div class="px-8 py-3 bg-blue-50 border-b border-blue-100 flex justify-between items-center text-xs text-blue-700 font-bold">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600 text-[18px]">info</span>
                            <span>Menampilkan pendapatan dari layanan: <span class="font-extrabold underline">{{ $serviceName }}</span></span>
                        </div>
                        <a href="{{ route('admin.finance.income') }}" class="text-blue-600 hover:text-blue-800 transition-colors uppercase tracking-widest text-[10px] font-black flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">close</span> Bersihkan Filter
                        </a>
                    </div>
                @endif

                <div class="flex items-center justify-between px-8 py-4 bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    <span>{{ $incomeHistory->total() }} records found</span>
                    <span>Page {{ $incomeHistory->currentPage() }} / {{ $incomeHistory->lastPage() }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <th class="px-8 py-4">Date & Time</th>
                                <th class="px-8 py-4">Source & Ref</th>
                                <th class="px-8 py-4">Method</th>
                                <th class="px-8 py-4 text-right">Amount</th>
                                <th class="px-8 py-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 bg-white">
                            @forelse($incomeHistory as $item)
                                <tr class="hover:bg-emerald-50/30 transition-colors">
                                    <td class="px-8 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-gray-900 font-bold text-[11px]">{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</span>
                                            <span class="text-[10px] text-gray-400 font-medium">{{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-4">
                                        @php
                                            $orderCode = '';
                                            if (preg_match('/ORD-[A-Z0-9]+/', $item->description, $matches)) {
                                                $orderCode = $matches[0];
                                            }
                                        @endphp

                                        @if($orderCode)
                                            @php
                                                $orderId = \App\Models\Order::where('order_code', $orderCode)->value('id');
                                            @endphp
                                            <a href="{{ $orderId ? route('admin.orders.show', $orderId) : '#' }}" class="flex flex-col gap-1 group/ref">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md uppercase tracking-tighter">
                                                        {{ $item->category }}
                                                    </span>
                                                    <span class="text-[10px] font-black text-white bg-blue-500 px-2 py-0.5 rounded-md uppercase shadow-sm">
                                                        {{ $orderCode }}
                                                    </span>
                                                </div>
                                                <span class="text-xs font-bold text-gray-500 truncate max-w-[250px] group-hover:text-blue-600">
                                                    {{ str_replace($orderCode, '', $item->description) }}
                                                </span>
                                            </a>
                                        @else
                                            <div class="flex flex-col gap-1">
                                                <span class="text-[10px] font-black text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md w-fit uppercase tracking-tighter">
                                                    {{ $item->category }}
                                                </span>
                                                <span class="text-xs font-bold text-gray-900 truncate max-w-[250px]">
                                                    {{ $item->description }}
                                                </span>
                                            </div>
                                        @endif
                                        @if($item->attachment)
                                            <div class="mt-1">
                                                <button @click="viewReceipt('{{ Storage::url($item->attachment) }}')" 
                                                    class="inline-flex items-center gap-1 text-[9px] font-black text-emerald-600 bg-emerald-50 hover:bg-emerald-100 px-2 py-0.5 rounded transition-all border border-emerald-100 uppercase tracking-wider w-fit"
                                                    title="View Receipt">
                                                    <span class="material-symbols-outlined text-[14px]">attachment</span> View Receipt
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-8 py-4">
                                        <div class="flex items-center gap-2">
                                            @if(str_contains(strtolower($item->description), 'stripe'))
                                                <span class="material-symbols-outlined text-indigo-500 text-[18px]">credit_card</span>
                                                <span class="text-[10px] font-black text-gray-600 uppercase">Stripe</span>
                                            @else
                                                <span class="material-symbols-outlined text-emerald-500 text-[18px]">payments</span>
                                                <span class="text-[10px] font-black text-gray-600 uppercase">Cash</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-8 py-4 text-right font-black text-emerald-600">
                                        + Rp {{ number_format($item->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-8 py-4">
                                        <div class="flex justify-center items-center gap-1.5">
                                            @if($orderCode && isset($orderId) && $orderId)
                                                <a href="{{ route('admin.orders.show', $orderId) }}" 
                                                    class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all border border-blue-100 flex items-center justify-center shadow-sm"
                                                    title="View Order">
                                                    <span class="material-symbols-outlined text-[18px]">shopping_basket</span>
                                                </a>
                                            @endif
                                            <button @click="openEditModal({{ json_encode($item) }})" 
                                                class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition-all border border-amber-100 flex items-center justify-center shadow-sm"
                                                title="Edit">
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </button>
                                            <button type="button"
                                                @click="
                                                    confirmTitle = 'Delete Income Record';
                                                    confirmMessage = 'Are you sure you want to delete this income record? This action cannot be undone.';
                                                    confirmActionUrl = '{{ route('admin.finance.destroy', $item->id) }}';
                                                    confirmActionMethod = 'DELETE';
                                                    confirmButtonText = 'Delete';
                                                    confirmButtonColorClass = 'bg-rose-600 hover:bg-rose-700';
                                                    showConfirmModal = true;
                                                "
                                                class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-all border border-rose-100 flex items-center justify-center shadow-sm"
                                                title="Delete">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="material-symbols-outlined text-5xl text-gray-200 mb-4">analytics</span>
                                            <p class="text-gray-400 font-bold">No income records found.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-8 py-6 bg-gray-50 border-t border-gray-100">
                    {{ $incomeHistory->links() }}
                </div>
            </div>

            {{-- Log Income Modal --}}
            <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <!-- Backdrop -->
                    <div x-show="showModal" 
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" 
                        @click="showModal = false"></div>

                    <!-- Modal Panel -->
                    <div x-show="showModal"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">
                        
                        <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center text-white">
                                    <span class="material-symbols-outlined text-lg">add_card</span>
                                </div>
                                Log New Income
                            </h3>
                            <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>

                        <div class="p-8">
                            <form action="{{ route('admin.finance.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                                @csrf
                                <input type="hidden" name="type" value="income">

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Date</label>
                                        <input type="date" name="date" class="w-full rounded-xl border-gray-200 text-sm font-bold text-gray-700 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm" value="{{ now()->format('Y-m-d') }}" required>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Category</label>
                                        <select name="category" class="w-full rounded-xl border-gray-200 text-sm font-bold text-gray-700 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm" required>
                                            <option value="Laundry Order">Laundry Order (Order Pembayaran)</option>
                                            <option value="Modal Awal">Modal Awal (Initial Capital)</option>
                                            <option value="Investasi">Investasi (Investment)</option>
                                            <option value="Lainnya">Lainnya (Others)</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Amount (Rp)</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-gray-400 font-bold text-xs">Rp</span>
                                        </div>
                                        <input type="number" name="amount" class="w-full pl-10 rounded-xl border-gray-200 text-sm font-black text-gray-900 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm" placeholder="0" required>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Payment Method</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        @foreach(['CASH', 'TRANSFER', 'STRIPE', 'LAINNYA'] as $method)
                                            <label class="relative flex items-center justify-center p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-all has-[:checked]:bg-emerald-50 has-[:checked]:border-emerald-200 has-[:checked]:ring-1 has-[:checked]:ring-emerald-200 group">
                                                <input type="radio" name="payment_method" value="{{ $method }}" class="hidden" {{ $method === 'CASH' ? 'checked' : '' }}>
                                                <span class="text-[10px] font-black text-gray-500 group-has-[:checked]:text-emerald-600 uppercase tracking-widest">{{ $method }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Description</label>
                                    <textarea name="description" rows="2" class="w-full rounded-xl border-gray-200 text-sm font-medium text-gray-700 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm" placeholder="What is this income from?"></textarea>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Attachment (Optional)</label>
                                    <div class="mt-1 flex flex-col items-center gap-4">
                                        <label class="w-full flex flex-col items-center justify-center h-28 border-2 border-dashed border-gray-200 rounded-2xl cursor-pointer hover:bg-gray-50 hover:border-emerald-500 transition-all group">
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <span class="material-symbols-outlined text-gray-400 group-hover:text-emerald-500 transition-colors text-3xl mb-1">cloud_upload</span>
                                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wide group-hover:text-emerald-600">Click to upload file</p>
                                                <p class="text-[8px] text-gray-300 mt-0.5">PNG, JPG, PDF (Max 2MB)</p>
                                            </div>
                                            <input type="file" name="attachment" class="hidden" @change="fileChosen">
                                        </label>
                                        
                                        <template x-if="imageUrl">
                                            <div class="relative w-full rounded-2xl overflow-hidden border border-gray-100 shadow-sm">
                                                <img :src="imageUrl" class="w-full h-32 object-cover">
                                                <button type="button" @click="imageUrl = null; $el.closest('form').querySelector('input[type=file]').value=''" class="absolute top-2 right-2 w-6 h-6 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/70 transition-colors">
                                                    <span class="material-symbols-outlined text-xs">close</span>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="pt-4 flex gap-3">
                                    <button type="button" @click="showModal = false" class="flex-1 bg-gray-50 border border-gray-200 text-gray-500 font-black py-3 rounded-xl hover:bg-gray-100 transition-all text-xs uppercase tracking-widest text-center">Cancel</button>
                                    <button type="submit" class="flex-1 bg-emerald-600 text-white font-black py-3 rounded-xl hover:bg-emerald-700 transition-all text-xs uppercase tracking-widest shadow-lg shadow-emerald-100">Record Income</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Receipt Preview Modal -->
        <div x-show="receiptModal" 
            class="fixed inset-0 z-50 overflow-y-auto"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none;">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm" @click="receiptModal = false"></div>

            <!-- Modal Content -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="receiptModal"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-90"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-90"
                    class="relative bg-white rounded-3xl shadow-2xl max-w-3xl w-full overflow-hidden border border-gray-100">

                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center">
                                <span class="material-symbols-outlined text-white text-base">receipt_long</span>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight">Income Attachment</h3>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Click outside to close</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a :href="receiptUrl" download 
                                @click="downloadLoading = true; setTimeout(() => downloadLoading = false, 2000)"
                                :class="{ 'opacity-70 pointer-events-none': downloadLoading }"
                                class="flex items-center gap-1.5 px-3 py-2 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-gray-700 transition-all">
                                <span x-show="!downloadLoading" class="material-symbols-outlined text-[14px]">download</span>
                                <svg x-show="downloadLoading" class="animate-spin h-[14px] w-[14px] text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" x-cloak>
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="downloadLoading ? 'Downloading...' : 'Download'"></span>
                            </a>
                            <button @click="receiptModal = false"
                                class="w-9 h-9 rounded-xl bg-gray-100 hover:bg-red-50 hover:text-red-600 flex items-center justify-center transition-all">
                                <span class="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-4 bg-gray-50 min-h-[400px] flex items-center justify-center">
                        <!-- Image -->
                        <template x-if="receiptType === 'image'">
                            <img :src="receiptUrl" alt="Attachment" 
                                class="max-w-full max-h-[65vh] object-contain rounded-2xl shadow-lg">
                        </template>

                        <!-- PDF -->
                        <template x-if="receiptType === 'pdf'">
                            <iframe :src="receiptUrl" class="w-full rounded-2xl" style="height: 65vh; border: none;"></iframe>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Income Modal -->
        <div x-show="editModal" 
            class="fixed inset-0 z-50 overflow-y-auto"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none;">
            
            <!-- Backdrop with Blur -->
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" @click="editModal = false"></div>

            <!-- Modal Panel -->
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="editModal"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">
                    
                    <!-- Header -->
                    <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-500 flex items-center justify-center text-white">
                                <span class="material-symbols-outlined text-lg">edit_document</span>
                            </div>
                            Edit Income Record
                        </h3>
                        <button @click="editModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <!-- Form Content -->
                    <div class="p-8">
                        <form :action="'{{ route('admin.finance.index') }}' + '/' + editData.id" method="POST" enctype="multipart/form-data" class="space-y-5">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Date</label>
                                    <input type="date" name="date" x-model="editData.date" class="w-full rounded-xl border-gray-200 text-sm font-bold text-gray-700 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm" required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Category</label>
                                    <select name="category" x-model="editData.category" class="w-full rounded-xl border-gray-200 text-sm font-bold text-gray-700 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm" required>
                                        <option value="Laundry Order">Laundry Order (Order Pembayaran)</option>
                                        <option value="Modal Awal">Modal Awal (Initial Capital)</option>
                                        <option value="Investasi">Investasi (Investment)</option>
                                        <option value="Lainnya">Lainnya (Others)</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Amount (Rp)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-gray-400 font-bold text-xs">Rp</span>
                                    </div>
                                    <input type="number" name="amount" x-model="editData.amount" class="w-full pl-10 rounded-xl border-gray-200 text-sm font-black text-gray-900 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm" required>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Payment Method</label>
                                <div class="grid grid-cols-4 gap-3">
                                    @foreach(['CASH', 'TRANSFER', 'STRIPE', 'LAINNYA'] as $method)
                                        <label class="relative flex items-center justify-center p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-all has-[:checked]:bg-emerald-50 has-[:checked]:border-emerald-200 has-[:checked]:ring-1 has-[:checked]:ring-emerald-200 group">
                                            <input type="radio" name="payment_method" value="{{ $method }}" x-model="editData.payment_method" class="hidden">
                                            <span class="text-[10px] font-black text-gray-500 group-has-[:checked]:text-emerald-600 uppercase tracking-widest">{{ $method }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Description</label>
                                <textarea name="description" x-model="editData.description" rows="2" class="w-full rounded-xl border-gray-200 text-sm font-medium text-gray-700 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm" placeholder="What is this income from?"></textarea>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Attachment (Optional)</label>
                                <div class="mt-1 flex flex-col gap-4">
                                    <label class="w-full flex flex-col items-center justify-center h-24 border-2 border-dashed border-gray-200 rounded-2xl cursor-pointer hover:bg-gray-50 hover:border-emerald-500 transition-all group">
                                        <div class="flex flex-col items-center justify-center pt-3 pb-3">
                                            <span class="material-symbols-outlined text-gray-400 group-hover:text-emerald-500 transition-colors text-2xl mb-0.5">cloud_upload</span>
                                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wide group-hover:text-emerald-600">Choose new file to replace</p>
                                            <p class="text-[8px] text-gray-300 mt-0.5">PNG, JPG, PDF (Max 2MB)</p>
                                        </div>
                                        <input type="file" name="attachment" class="hidden" @change="editFileChosen">
                                    </label>
                                    
                                    <template x-if="editImageUrl">
                                        <div class="relative w-full rounded-2xl overflow-hidden border border-gray-100 shadow-sm">
                                            <!-- Check if pdf -->
                                            <template x-if="editImageUrl.toLowerCase().endsWith('.pdf')">
                                                <div class="p-4 bg-gray-50 flex items-center justify-center gap-2">
                                                    <span class="material-symbols-outlined text-red-500">picture_as_pdf</span>
                                                    <span class="text-xs font-bold text-gray-600 uppercase">PDF Document Attached</span>
                                                </div>
                                            </template>
                                            <template x-if="!editImageUrl.toLowerCase().endsWith('.pdf')">
                                                <img :src="editImageUrl" class="w-full h-32 object-cover">
                                            </template>
                                            <button type="button" @click="editImageUrl = null; editData.attachment_url = null" class="absolute top-2 right-2 w-6 h-6 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/70 transition-colors">
                                                <span class="material-symbols-outlined text-xs">close</span>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="pt-4 flex gap-3">
                                <button type="button" @click="editModal = false" class="flex-1 bg-gray-50 border border-gray-200 text-gray-500 font-black py-3 rounded-xl hover:bg-gray-100 transition-all text-xs uppercase tracking-widest text-center">Cancel</button>
                                <button type="submit" class="flex-1 bg-emerald-600 text-white font-black py-3 rounded-xl hover:bg-emerald-700 transition-all text-xs uppercase tracking-widest shadow-lg shadow-emerald-100">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

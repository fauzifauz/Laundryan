<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
            <div>
                <h2 class="font-black text-2xl text-gray-900 leading-tight">Delivery Fees</h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Set tiered delivery billing ranges based on km distance</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{
        showModal: false,
        modalMode: 'create',
        formAction: '',
        form: { id: '', min_distance: 0, max_distance: 0, fee: 0, min_fee: 0, max_fee: '', is_active: true },
        showDeleteModal: false,
        deleteAction: '',
        deleteItemName: '',
        confirmDelete(actionUrl, name) {
            this.deleteAction = actionUrl;
            this.deleteItemName = name;
            this.showDeleteModal = true;
        },

        openAdd() {
            this.modalMode = 'create';
            this.formAction = '{{ route('admin.pricing.delivery-fees.store') }}';
            this.form = { id: '', min_distance: 0, max_distance: 0, fee: 0, min_fee: 0, max_fee: '', is_active: true };
            this.showModal = true;
        },
        openEdit(df) {
            this.modalMode = 'edit';
            this.formAction = '/admin/pricing/delivery-fees/' + df.id;
            this.form = {
                id: df.id, min_distance: df.min_distance, max_distance: df.max_distance,
                fee: df.fee, min_fee: df.min_fee, max_fee: df.max_fee || '', is_active: !!df.is_active
            };
            this.showModal = true;
        }
    }">
        <div class="max-w-7xl mx-auto space-y-8">

            {{-- Alerts --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                    x-transition:enter="transform ease-out duration-300 transition"
                    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed top-6 right-6 z-50 max-w-sm w-full bg-gradient-to-r from-emerald-600 to-teal-600 rounded-3xl p-5 shadow-2xl text-white flex items-center justify-between overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="w-10 h-10 rounded-2xl bg-white/15 border border-white/20 flex items-center justify-center shadow-inner">
                            <span class="material-symbols-outlined text-white text-xl">check_circle</span>
                        </div>
                        <div>
                            <h4 class="font-black text-xs uppercase tracking-wider">Changes Saved</h4>
                            <p class="text-[11px] text-emerald-50 font-medium mt-0.5">{{ session('success') }}</p>
                        </div>
                    </div>
                    <button @click="show = false" class="text-white/60 hover:text-white transition-colors p-2 rounded-xl hover:bg-white/10 relative z-10">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
            @endif
            @if($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl shadow-sm">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-rose-500">error</span>
                        <span class="text-sm font-bold">Please fix the following errors:</span>
                    </div>
                    <ul class="list-disc pl-8 text-xs font-semibold space-y-1">
                        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- Action Header --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-bold text-gray-500">{{ $deliveryFees->total() }} tier(s) configured</span>
                    @if(session('success') && session('action_status'))
                        <div x-data="{ showBadge: true }" x-init="setTimeout(() => showBadge = false, 5000)" x-show="showBadge" 
                             class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold rounded-xl transition-all duration-300">
                            <span class="text-emerald-500 font-extrabold">✓</span>
                            @if(session('action_status') === 'added')
                                <span>Successfully Added</span>
                            @elseif(session('action_status') === 'updated')
                                <span>Successfully Updated</span>
                            @elseif(session('action_status') === 'deleted')
                                <span>Successfully Deleted</span>
                            @endif
                        </div>
                    @endif
                </div>
                <button @click="openAdd()"
                    class="inline-flex items-center gap-2.5 px-6 py-3.5 bg-gradient-to-r from-[#005bc0] to-[#004899] text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:shadow-[0_8px_30px_rgb(0,91,192,0.35)] hover:-translate-y-0.5 transition-all shadow-md">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Add Distance Tier
                </button>
            </div>

            {{-- Cards Grid --}}
            @if($deliveryFees->count())
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($deliveryFees as $df)
                        <div class="group bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 overflow-hidden">

                            {{-- Color Accent Bar --}}
                            <div class="h-2 w-full {{ $df->is_active ? 'bg-gradient-to-r from-blue-500 to-indigo-600' : 'bg-gradient-to-r from-gray-200 to-gray-300' }}"></div>

                            <div class="p-6 space-y-6">
                                {{-- Distance Range --}}
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center shrink-0 text-[#005bc0]">
                                        <span class="material-symbols-outlined text-[26px]">local_shipping</span>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Distance Range</p>
                                        <p class="font-black text-gray-900 text-xl mt-0.5">
                                            {{ number_format($df->min_distance, 1) }} – {{ number_format($df->max_distance, 1) }}
                                            <span class="text-xs font-extrabold text-gray-400 lowercase">km</span>
                                        </p>
                                    </div>
                                </div>

                                {{-- Pricing Details Grid --}}
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="bg-blue-50/50 rounded-2xl p-3.5 text-center border border-blue-50">
                                        <p class="text-[8px] font-black text-blue-500 uppercase tracking-widest">Per KM</p>
                                        <p class="font-black text-[#005bc0] text-xs mt-1.5">Rp {{ number_format($df->fee, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="bg-emerald-50/50 rounded-2xl p-3.5 text-center border border-emerald-50">
                                        <p class="text-[8px] font-black text-emerald-500 uppercase tracking-widest">Min Fee</p>
                                        <p class="font-black text-emerald-700 text-xs mt-1.5">Rp {{ number_format($df->min_fee, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="bg-amber-50/50 rounded-2xl p-3.5 text-center border border-amber-50">
                                        <p class="text-[8px] font-black text-amber-500 uppercase tracking-widest">Max Fee</p>
                                        <p class="font-black text-amber-700 text-xs mt-1.5">
                                            {{ $df->max_fee ? 'Rp ' . number_format($df->max_fee, 0, ',', '.') : 'Unlimited' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Actions Row --}}
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <form action="{{ route('admin.pricing.delivery-fees.toggle', $df) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border transition-all cursor-pointer shadow-sm
                                            {{ $df->is_active
                                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
                                                : 'bg-gray-50 text-gray-400 border-gray-200 hover:bg-gray-100' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $df->is_active ? 'bg-emerald-500 shadow-[0_0_6px_#10B981]' : 'bg-gray-400' }}"></span>
                                            {{ $df->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                    <div class="flex items-center gap-1">
                                        <button @click="openEdit({{ $df }})"
                                            class="p-2.5 hover:bg-blue-50 text-blue-600 rounded-2xl transition-all" title="Edit Tier">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>
                                        <button type="button" @click="confirmDelete('{{ route('admin.pricing.delivery-fees.destroy', $df) }}', '{{ $df->min_distance }} - {{ $df->max_distance }} km')"
                                            class="p-2.5 hover:bg-rose-50 text-rose-500 rounded-2xl transition-all" title="Delete Tier">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-8">{{ $deliveryFees->links() }}</div>
            @else
                <div class="flex flex-col items-center justify-center py-24 bg-white rounded-3xl border border-gray-100 shadow-sm gap-5">
                    <div class="w-24 h-24 rounded-full bg-blue-50 flex items-center justify-center text-[#005bc0] shadow-inner">
                        <span class="material-symbols-outlined text-[48px] font-light">local_shipping</span>
                    </div>
                    <div class="text-center space-y-1">
                        <p class="font-black text-gray-800 text-lg">No delivery tiers yet</p>
                        <p class="text-sm text-gray-400 font-semibold">Add distance-based delivery fee tiers to start billing for pickup & drop-off</p>
                    </div>
                    <button @click="openAdd()"
                        class="inline-flex items-center gap-2.5 px-6 py-3 bg-[#005bc0] text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-blue-700 transition-all shadow-md">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Add Distance Tier
                    </button>
                </div>
            @endif
        </div>

        {{-- ========== MODAL ========== --}}
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 backdrop-blur-0"
             x-transition:enter-end="opacity-100 backdrop-blur-md"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 backdrop-blur-md"
             x-transition:leave-end="opacity-0 backdrop-blur-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             style="display:none;">
            <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-gray-100 overflow-hidden transform transition-all"
                 x-show="showModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-8"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-8"
                 @click.away="showModal = false">

                {{-- Modal Header --}}
                <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex justify-between items-center">
                    <div>
                        <h4 class="font-black text-xl text-gray-900" x-text="modalMode === 'create' ? 'Add Distance Tier' : 'Edit Distance Tier'"></h4>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Distance-based delivery pricing</p>
                    </div>
                    <button @click="showModal = false"
                        class="w-10 h-10 flex items-center justify-center rounded-2xl hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-all">
                        <span class="material-symbols-outlined text-[22px]">close</span>
                    </button>
                </div>

                <form :action="formAction" method="POST" class="p-8 space-y-6">
                    @csrf
                    <template x-if="modalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Min Distance (km) <span class="text-rose-500">*</span></label>
                            <input type="number" name="min_distance" x-model="form.min_distance" min="0" step="any" required
                                   class="block w-full rounded-2xl border-gray-200 focus:border-[#005bc0] focus:ring focus:ring-[#005bc0]/20 text-sm py-3.5 px-4 transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Max Distance (km) <span class="text-rose-500">*</span></label>
                            <input type="number" name="max_distance" x-model="form.max_distance" min="0" step="any" required
                                   class="block w-full rounded-2xl border-gray-200 focus:border-[#005bc0] focus:ring focus:ring-[#005bc0]/20 text-sm py-3.5 px-4 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Fee per KM <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-gray-400">Rp</span>
                            <input type="number" name="fee" x-model="form.fee" min="0" step="any" required
                                   class="block w-full pl-11 pr-4 rounded-2xl border-gray-200 focus:border-[#005bc0] focus:ring focus:ring-[#005bc0]/20 text-sm py-3.5 transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Minimum Fee <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-black text-gray-400">Rp</span>
                                <input type="number" name="min_fee" x-model="form.min_fee" min="0" step="any" required
                                       class="block w-full pl-10 pr-4 rounded-2xl border-gray-200 focus:border-[#005bc0] focus:ring focus:ring-[#005bc0]/20 text-sm py-3.5 transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Maximum Fee <span class="text-gray-300 font-bold">(optional)</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-black text-gray-400">Rp</span>
                                <input type="number" name="max_fee" x-model="form.max_fee" min="0" step="any"
                                       class="block w-full pl-10 pr-4 rounded-2xl border-gray-200 focus:border-[#005bc0] focus:ring focus:ring-[#005bc0]/20 text-sm py-3.5 transition-all"
                                       placeholder="No maximum cap">
                            </div>
                        </div>
                    </div>

                    {{-- Custom Active Toggle Switch --}}
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-gray-100">
                        <div class="space-y-0.5">
                            <label class="text-sm font-bold text-gray-700 block">Tier Status</label>
                            <span class="text-[10px] text-gray-400 font-semibold block">Enable this distance tier for courier delivery billing</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#005bc0]"></div>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showModal = false"
                            class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-black uppercase tracking-widest rounded-2xl transition-all">Cancel</button>
                        <button type="submit"
                            class="px-6 py-3 bg-[#005bc0] hover:bg-blue-700 text-white text-xs font-black uppercase tracking-widest rounded-2xl transition-all shadow-md hover:shadow-lg">
                            <span x-text="modalMode === 'create' ? 'Create Tier' : 'Save Changes'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Custom Delete Confirmation Modal --}}
        <div x-show="showDeleteModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             style="display:none;">
            <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-gray-100 overflow-hidden"
                 @click.away="showDeleteModal = false">
                
                {{-- Modal Header --}}
                <div class="px-7 py-5 border-b border-gray-100 bg-rose-50/30 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-rose-500">warning</span>
                        <h4 class="font-black text-lg text-gray-900">Delete Confirmation</h4>
                    </div>
                    <button @click="showDeleteModal = false"
                        class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-all">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-7 space-y-6">
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Are you sure you want to delete <strong class="text-gray-900" x-text="deleteItemName"></strong>? This action cannot be undone and will permanently remove the record.
                    </p>

                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-50">
                        <button type="button" @click="showDeleteModal = false"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-black uppercase tracking-widest rounded-xl transition-all">
                            Cancel
                        </button>
                        <form :action="deleteAction" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-sm hover:shadow-md">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

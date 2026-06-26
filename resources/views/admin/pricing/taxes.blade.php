<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
            <div>
                <h2 class="font-black text-2xl text-gray-900 leading-tight">Tax Configurations</h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Define tax rates applied automatically at checkout</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10" x-data="{
        showModal: false,
        modalMode: 'create',
        formAction: '',
        form: { id: '', name: '', percentage: 0, is_active: true },
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
            this.formAction = '{{ route('admin.pricing.taxes.store') }}';
            this.form = { id: '', name: '', percentage: 0, is_active: true };
            this.showModal = true;
        },
        openEdit(tax) {
            this.modalMode = 'edit';
            this.formAction = '/admin/pricing/taxes/' + tax.id;
            this.form = {
                id: tax.id,
                name: tax.name,
                percentage: tax.percentage,
                is_active: !!tax.is_active
            };
            this.showModal = true;
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

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

            {{-- Header Bar --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-bold text-gray-500">{{ $taxes->total() }} tax rate(s) configured</span>
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
                    class="inline-flex items-center gap-2 px-5 py-3 bg-[#005bc0] text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-blue-700 hover:shadow-lg hover:-translate-y-0.5 transition-all shadow-md">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Add Tax Rate
                </button>
            </div>

            {{-- Tax Cards --}}
            @if($taxes->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    @foreach($taxes as $tax)
                        <div class="group bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">

                            {{-- Color Accent Bar --}}
                            <div class="h-1.5 w-full {{ $tax->is_active ? 'bg-gradient-to-r from-amber-400 to-orange-400' : 'bg-gradient-to-r from-gray-200 to-gray-300' }}"></div>

                            <div class="p-6 flex flex-col gap-4">

                                {{-- Icon & Name --}}
                                <div class="flex items-start gap-4">
                                    <div class="w-14 h-14 rounded-2xl {{ $tax->is_active ? 'bg-amber-50' : 'bg-gray-50' }} flex items-center justify-center shrink-0 transition-colors">
                                        <span class="material-symbols-outlined text-[26px] {{ $tax->is_active ? 'text-amber-500' : 'text-gray-400' }}">percent</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-black text-gray-900 text-base leading-snug truncate">{{ $tax->name }}</h4>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Tax Rate</p>
                                    </div>
                                </div>

                                {{-- Big Percentage Display --}}
                                <div class="bg-gradient-to-br {{ $tax->is_active ? 'from-amber-50 to-orange-50 border-amber-100' : 'from-gray-50 to-gray-100 border-gray-200' }} border rounded-2xl px-5 py-4 text-center transition-all">
                                    <p class="font-black text-4xl {{ $tax->is_active ? 'text-amber-600' : 'text-gray-400' }}">
                                        {{ number_format($tax->percentage, 2) }}<span class="text-2xl">%</span>
                                    </p>
                                    <p class="text-[10px] font-bold {{ $tax->is_active ? 'text-amber-400' : 'text-gray-400' }} uppercase tracking-widest mt-1">Applied to subtotal</p>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center justify-between pt-1 border-t border-gray-50">
                                    <form action="{{ route('admin.pricing.taxes.toggle', $tax) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border transition-all cursor-pointer
                                            {{ $tax->is_active
                                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
                                                : 'bg-gray-50 text-gray-400 border-gray-200 hover:bg-gray-100' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $tax->is_active ? 'bg-emerald-500 shadow-[0_0_6px_#10B981]' : 'bg-gray-400' }}"></span>
                                            {{ $tax->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                    <div class="flex items-center gap-1">
                                        <button @click="openEdit({{ $tax }})"
                                            class="p-2.5 hover:bg-blue-50 text-blue-600 rounded-xl transition-all" title="Edit">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>
                                        <button type="button" @click="confirmDelete('{{ route('admin.pricing.taxes.destroy', $tax) }}', '{{ addslashes($tax->name) }}')"
                                            class="p-2.5 hover:bg-rose-50 text-rose-500 rounded-xl transition-all" title="Delete">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $taxes->links() }}</div>
            @else
                <div class="flex flex-col items-center justify-center py-24 bg-white rounded-3xl border border-gray-100 shadow-sm gap-4">
                    <div class="w-20 h-20 rounded-full bg-amber-50 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[40px] text-amber-300">percent</span>
                    </div>
                    <div class="text-center">
                        <p class="font-black text-gray-700 text-lg">No tax rates yet</p>
                        <p class="text-sm text-gray-400 font-medium mt-1">Add tax rates that will be automatically applied at checkout</p>
                    </div>
                    <button @click="openAdd()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#005bc0] text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-blue-700 transition-all mt-2">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Add First Tax Rate
                    </button>
                </div>
            @endif
        </div>

        {{-- ========== MODAL ========== --}}
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             style="display:none;">
            <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-gray-100 overflow-hidden"
                 @click.away="showModal = false">

                <div class="px-7 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex justify-between items-center">
                    <div>
                        <h4 class="font-black text-lg text-gray-900" x-text="modalMode === 'create' ? 'Add Tax Rate' : 'Edit Tax Rate'"></h4>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Tax configuration</p>
                    </div>
                    <button @click="showModal = false"
                        class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-all">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form :action="formAction" method="POST" class="p-7 space-y-5">
                    @csrf
                    <template x-if="modalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Tax Name <span class="text-rose-400">*</span></label>
                        <input type="text" name="name" x-model="form.name" required
                               class="block w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm py-3 transition-all"
                               placeholder="e.g. PPN, PB1, Service Tax">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Tax Percentage (%) <span class="text-rose-400">*</span></label>
                        <div class="relative">
                            <input type="number" name="percentage" x-model="form.percentage"
                                   min="0" max="100" step="0.01" required
                                   class="block w-full pr-12 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm py-3 transition-all">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-black text-gray-400">%</span>
                        </div>

                        {{-- Live Preview --}}
                        <div class="mt-3 p-4 bg-amber-50 border border-amber-100 rounded-2xl" x-show="form.percentage > 0">
                            <p class="text-[10px] font-black text-amber-400 uppercase tracking-widest">Example Calculation</p>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-xs font-bold text-gray-600">On Rp 100.000 subtotal:</span>
                                <span class="text-sm font-black text-amber-700">
                                    + Rp <span x-text="(100000 * form.percentage / 100).toLocaleString('id-ID')"></span>
                                </span>
                            </div>
                            <div class="flex justify-between items-center mt-1 pt-1 border-t border-amber-100">
                                <span class="text-xs font-bold text-gray-400">Total:</span>
                                <span class="text-sm font-black text-gray-700">
                                    Rp <span x-text="(100000 + 100000 * form.percentage / 100).toLocaleString('id-ID')"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="tax_is_active" name="is_active" value="1" x-model="form.is_active"
                               class="w-5 h-5 rounded-lg border-gray-200 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        <label for="tax_is_active" class="text-sm font-bold text-gray-700 cursor-pointer select-none">Active (applied at checkout)</label>
                    </div>

                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-50">
                        <button type="button" @click="showModal = false"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-black uppercase tracking-widest rounded-xl transition-all">Cancel</button>
                        <button type="submit"
                            class="px-6 py-2.5 bg-[#005bc0] hover:bg-blue-700 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-sm hover:shadow-md">
                            <span x-text="modalMode === 'create' ? 'Create Tax Rate' : 'Save Changes'"></span>
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

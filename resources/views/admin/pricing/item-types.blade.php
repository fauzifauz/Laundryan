<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
            <div>
                <h2 class="font-black text-2xl text-gray-900 leading-tight">Item Types</h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Configure pricing modifiers per laundry item category</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10" x-data="{
        showItemTypeModal: false,
        itemTypeModalMode: 'create',
        itemTypeAction: '',
        itemTypeForm: { id: '', name: '', description: '', base_price: 0, is_active: true, photo_url: null },
        previewPhoto: null,
        showDeleteModal: false,
        deleteAction: '',
        deleteItemName: '',
        confirmDelete(actionUrl, name) {
            this.deleteAction = actionUrl;
            this.deleteItemName = name;
            this.showDeleteModal = true;
        },

        openAddItemType() {
            this.itemTypeModalMode = 'create';
            this.itemTypeAction = '{{ route('admin.pricing.item-types.store') }}';
            this.itemTypeForm = { id: '', name: '', description: '', base_price: 0, is_active: true, photo_url: null };
            this.previewPhoto = null;
            this.$nextTick(() => {
                let fi = document.getElementById('itemPhotoInput');
                if (fi) fi.value = '';
            });
            this.showItemTypeModal = true;
        },
        openEditItemType(itemType) {
            this.itemTypeModalMode = 'edit';
            this.itemTypeAction = '/admin/pricing/item-types/' + itemType.id;
            this.itemTypeForm = {
                id: itemType.id,
                name: itemType.name,
                description: itemType.description || '',
                base_price: itemType.base_price,
                is_active: !!itemType.is_active,
                photo_url: itemType.photo ? '/storage/' + itemType.photo : null
            };
            this.previewPhoto = itemType.photo ? '/storage/' + itemType.photo : null;
            this.$nextTick(() => {
                let fi = document.getElementById('itemPhotoInput');
                if (fi) fi.value = '';
            });
            this.showItemTypeModal = true;
        },
        handlePhotoChange(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => { this.previewPhoto = e.target.result; };
                reader.readAsDataURL(file);
            }
        },
        clearPhoto() {
            this.previewPhoto = null;
            this.itemTypeForm.photo_url = null;
            let fi = document.getElementById('itemPhotoInput');
            if (fi) fi.value = '';
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
                    <span class="text-sm font-bold text-gray-500">{{ $itemTypes->total() }} item type(s) configured</span>
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
                <button @click="openAddItemType()"
                    class="inline-flex items-center gap-2 px-5 py-3 bg-[#005bc0] text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-blue-700 hover:shadow-lg hover:-translate-y-0.5 transition-all shadow-md">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Add Item Type
                </button>
            </div>

            {{-- Cards Grid --}}
            @if($itemTypes->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($itemTypes as $itemType)
                        <div class="group bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col">

                            {{-- Photo Area --}}
                            <div class="relative h-52 bg-gradient-to-br from-purple-50 to-violet-100 overflow-hidden">
                                @if($itemType->photo)
                                    <img src="{{ asset('storage/' . $itemType->photo) }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-[52px] text-purple-300">category</span>
                                        <span class="text-[10px] font-bold text-purple-300 uppercase tracking-widest">No Image</span>
                                    </div>
                                @endif

                                {{-- Status Badge --}}
                                <div class="absolute top-3 right-3">
                                    <form action="{{ route('admin.pricing.item-types.toggle', $itemType) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border backdrop-blur-sm transition-all cursor-pointer
                                            {{ $itemType->is_active
                                                ? 'bg-emerald-500/90 text-white border-emerald-400 hover:bg-emerald-600'
                                                : 'bg-white/90 text-gray-500 border-gray-200 hover:bg-gray-100' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $itemType->is_active ? 'bg-white shadow-[0_0_6px_white]' : 'bg-gray-400' }}"></span>
                                            {{ $itemType->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="p-5 flex flex-col flex-1 gap-3">
                                <div class="flex-1">
                                    <h4 class="font-black text-gray-900 text-base leading-snug">{{ $itemType->name }}</h4>
                                    @if($itemType->description)
                                        <p class="text-xs text-gray-400 font-medium mt-1 leading-relaxed line-clamp-2">{{ $itemType->description }}</p>
                                    @endif
                                </div>

                                <div class="pt-3 border-t border-gray-50 flex items-center justify-between">
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Price Modifier</p>
                                        <p class="text-lg font-black text-purple-600 mt-0.5">Rp {{ number_format($itemType->base_price, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button @click="openEditItemType({{ $itemType }})"
                                            class="p-2.5 hover:bg-blue-50 text-blue-600 rounded-xl transition-all" title="Edit">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>
                                        <button type="button" @click="confirmDelete('{{ route('admin.pricing.item-types.destroy', $itemType) }}', '{{ addslashes($itemType->name) }}')"
                                            class="p-2.5 hover:bg-rose-50 text-rose-500 rounded-xl transition-all" title="Delete">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $itemTypes->links() }}</div>
            @else
                <div class="flex flex-col items-center justify-center py-24 bg-white rounded-3xl border border-gray-100 shadow-sm gap-4">
                    <div class="w-20 h-20 rounded-full bg-purple-50 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[40px] text-purple-300">category</span>
                    </div>
                    <div class="text-center">
                        <p class="font-black text-gray-700 text-lg">No item types yet</p>
                        <p class="text-sm text-gray-400 font-medium mt-1">Add your first item type to configure pricing modifiers</p>
                    </div>
                    <button @click="openAddItemType()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#005bc0] text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-blue-700 transition-all mt-2">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Add First Item Type
                    </button>
                </div>
            @endif
        </div>

        {{-- ========== MODAL ========== --}}
        <div x-show="showItemTypeModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             style="display:none;">
            <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl border border-gray-100 overflow-hidden"
                 @click.away="showItemTypeModal = false">

                <div class="px-7 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex justify-between items-center">
                    <div>
                        <h4 class="font-black text-lg text-gray-900" x-text="itemTypeModalMode === 'create' ? 'Add New Item Type' : 'Edit Item Type'"></h4>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Item type configuration</p>
                    </div>
                    <button @click="showItemTypeModal = false"
                        class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-all">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form :action="itemTypeAction" method="POST" enctype="multipart/form-data" class="p-7 space-y-5">
                    @csrf
                    <template x-if="itemTypeModalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    {{-- Photo Upload with Preview --}}
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Item Photo</label>

                        <div class="relative w-full h-52 rounded-2xl overflow-hidden bg-gradient-to-br from-purple-50 to-violet-100 border-2 border-dashed border-purple-200 mb-3 group cursor-pointer"
                             @click="$refs.itemPhotoInput.click()">
                            <template x-if="previewPhoto">
                                <img :src="previewPhoto" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!previewPhoto">
                                <div class="w-full h-full flex flex-col items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-[40px] text-purple-300 group-hover:text-purple-400 transition-colors">add_photo_alternate</span>
                                    <span class="text-xs font-bold text-purple-400 group-hover:text-purple-500 transition-colors">Click to select photo</span>
                                    <span class="text-[10px] text-purple-300">JPG, PNG, WEBP – max 2MB</span>
                                </div>
                            </template>
                            <template x-if="previewPhoto">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-white text-[28px]">photo_camera</span>
                                    <span class="text-white text-xs font-black">Change Photo</span>
                                </div>
                            </template>
                        </div>

                        <input type="file" id="itemPhotoInput" x-ref="itemPhotoInput" name="photo" accept="image/*"
                               class="hidden"
                               @change="handlePhotoChange($event)">

                        <div x-show="previewPhoto" class="flex items-center gap-2 mt-2">
                            <button type="button" @click="clearPhoto()"
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-500 hover:text-rose-700 transition-colors">
                                <span class="material-symbols-outlined text-[16px]">delete</span>
                                Remove photo
                            </button>
                        </div>
                        <input type="hidden" name="remove_photo" :value="(!previewPhoto && itemTypeModalMode === 'edit') ? '1' : '0'">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Item Type Name <span class="text-rose-400">*</span></label>
                        <input type="text" name="name" x-model="itemTypeForm.name" required
                               class="block w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm py-3 transition-all"
                               placeholder="e.g. Sepatu, Tas, Jas, Linen">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Description</label>
                        <textarea name="description" x-model="itemTypeForm.description" rows="2"
                                  class="block w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm py-3 transition-all resize-none"
                                  placeholder="Brief description of this item type..."></textarea>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Price Modifier (Rp) <span class="text-rose-400">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-gray-400">Rp</span>
                            <input type="number" name="base_price" x-model="itemTypeForm.base_price" min="0" step="any" required
                                   class="block w-full pl-10 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm py-3 transition-all">
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="item_is_active" name="is_active" value="1" x-model="itemTypeForm.is_active"
                               class="w-5 h-5 rounded-lg border-gray-200 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        <label for="item_is_active" class="text-sm font-bold text-gray-700 cursor-pointer select-none">Active (visible to customers)</label>
                    </div>

                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-50">
                        <button type="button" @click="showItemTypeModal = false"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-black uppercase tracking-widest rounded-xl transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-2.5 bg-[#005bc0] hover:bg-blue-700 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-sm hover:shadow-md">
                            <span x-text="itemTypeModalMode === 'create' ? 'Create Item Type' : 'Save Changes'"></span>
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

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
            <div>
                <h2 class="font-black text-2xl text-gray-900 leading-tight">Laundry Services</h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Manage base laundry service workflows and pricing</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10" x-data="{
        showServiceModal: false,
        serviceModalMode: 'create',
        serviceAction: '',
        serviceForm: { id: '', name: '', description: '', base_price: 0, is_active: true, photo_url: null },
        previewPhoto: null,
        showDeleteModal: false,
        deleteAction: '',
        deleteItemName: '',
        confirmDelete(actionUrl, name) {
            this.deleteAction = actionUrl;
            this.deleteItemName = name;
            this.showDeleteModal = true;
        },

        openAddService() {
            this.serviceModalMode = 'create';
            this.serviceAction = '{{ route('admin.pricing.services.store') }}';
            this.serviceForm = { id: '', name: '', description: '', base_price: 0, is_active: true, photo_url: null };
            this.previewPhoto = null;
            this.$nextTick(() => {
                let fi = document.getElementById('photoInput');
                if (fi) fi.value = '';
            });
            this.showServiceModal = true;
        },
        openEditService(service) {
            this.serviceModalMode = 'edit';
            this.serviceAction = '/admin/pricing/services/' + service.id;
            this.serviceForm = {
                id: service.id,
                name: service.name,
                description: service.description || '',
                base_price: service.base_price,
                is_active: !!service.is_active,
                photo_url: service.photo ? '/storage/' + service.photo : null
            };
            this.previewPhoto = service.photo ? '/storage/' + service.photo : null;
            this.$nextTick(() => {
                let fi = document.getElementById('photoInput');
                if (fi) fi.value = '';
            });
            this.showServiceModal = true;
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
            this.serviceForm.photo_url = null;
            let fi = document.getElementById('photoInput');
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
                    <span class="text-sm font-bold text-gray-500">{{ $services->total() }} service(s) configured</span>
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
                <button @click="openAddService()"
                    class="inline-flex items-center gap-2 px-5 py-3 bg-[#005bc0] text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-blue-700 hover:shadow-lg hover:-translate-y-0.5 transition-all shadow-md">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Add Service
                </button>
            </div>

            {{-- Cards Grid --}}
            @if($services->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($services as $service)
                        <div class="group bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col">

                            {{-- Photo Area --}}
                            <div class="relative h-52 bg-gradient-to-br from-blue-50 to-indigo-100 overflow-hidden">
                                @if($service->photo)
                                    <img src="{{ asset('storage/' . $service->photo) }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-[52px] text-blue-300">local_laundry_service</span>
                                        <span class="text-[10px] font-bold text-blue-300 uppercase tracking-widest">No Image</span>
                                    </div>
                                @endif

                                {{-- Status Badge --}}
                                <div class="absolute top-3 right-3">
                                    <form action="{{ route('admin.pricing.services.toggle', $service) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border backdrop-blur-sm transition-all cursor-pointer
                                            {{ $service->is_active
                                                ? 'bg-emerald-500/90 text-white border-emerald-400 hover:bg-emerald-600'
                                                : 'bg-white/90 text-gray-500 border-gray-200 hover:bg-gray-100' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $service->is_active ? 'bg-white shadow-[0_0_6px_white]' : 'bg-gray-400' }}"></span>
                                            {{ $service->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="p-5 flex flex-col flex-1 gap-3">
                                <div class="flex-1">
                                    <h4 class="font-black text-gray-900 text-base leading-snug">{{ $service->name }}</h4>
                                    @if($service->description)
                                        <p class="text-xs text-gray-400 font-medium mt-1 leading-relaxed line-clamp-2">{{ $service->description }}</p>
                                    @endif
                                </div>

                                <div class="pt-3 border-t border-gray-50 flex items-center justify-between">
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Base Price</p>
                                        <p class="text-lg font-black text-[#005bc0] mt-0.5">Rp {{ number_format($service->base_price, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button @click="openEditService({{ $service }})"
                                            class="p-2.5 hover:bg-blue-50 text-blue-600 rounded-xl transition-all" title="Edit">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>
                                        <button type="button" @click="confirmDelete('{{ route('admin.pricing.services.destroy', $service) }}', '{{ addslashes($service->name) }}')"
                                            class="p-2.5 hover:bg-rose-50 text-rose-500 rounded-xl transition-all" title="Delete">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $services->links() }}</div>
            @else
                <div class="flex flex-col items-center justify-center py-24 bg-white rounded-3xl border border-gray-100 shadow-sm gap-4">
                    <div class="w-20 h-20 rounded-full bg-blue-50 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[40px] text-blue-300">local_laundry_service</span>
                    </div>
                    <div class="text-center">
                        <p class="font-black text-gray-700 text-lg">No services yet</p>
                        <p class="text-sm text-gray-400 font-medium mt-1">Add your first laundry service to get started</p>
                    </div>
                    <button @click="openAddService()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#005bc0] text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-blue-700 transition-all mt-2">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Add First Service
                    </button>
                </div>
            @endif
        </div>

        {{-- ========== MODAL ========== --}}
        <div x-show="showServiceModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             style="display:none;">
            <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl border border-gray-100 overflow-hidden"
                 @click.away="showServiceModal = false">

                {{-- Modal Header --}}
                <div class="px-7 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex justify-between items-center">
                    <div>
                        <h4 class="font-black text-lg text-gray-900" x-text="serviceModalMode === 'create' ? 'Add New Service' : 'Edit Service'"></h4>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Laundry service configuration</p>
                    </div>
                    <button @click="showServiceModal = false"
                        class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-all">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form :action="serviceAction" method="POST" enctype="multipart/form-data" class="p-7 space-y-5">
                    @csrf
                    <template x-if="serviceModalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    {{-- Photo Upload with Preview --}}
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Service Photo</label>

                        {{-- Preview Area --}}
                        <div class="relative w-full h-52 rounded-2xl overflow-hidden bg-gradient-to-br from-blue-50 to-indigo-100 border-2 border-dashed border-blue-200 mb-3 group cursor-pointer"
                             @click="$refs.photoInput.click()">
                            <template x-if="previewPhoto">
                                <img :src="previewPhoto" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!previewPhoto">
                                <div class="w-full h-full flex flex-col items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-[40px] text-blue-300 group-hover:text-blue-400 transition-colors">add_photo_alternate</span>
                                    <span class="text-xs font-bold text-blue-400 group-hover:text-blue-500 transition-colors">Click to select photo</span>
                                    <span class="text-[10px] text-blue-300">JPG, PNG, WEBP – max 2MB</span>
                                </div>
                            </template>
                            {{-- Overlay on hover when has image --}}
                            <template x-if="previewPhoto">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-white text-[28px]">photo_camera</span>
                                    <span class="text-white text-xs font-black">Change Photo</span>
                                </div>
                            </template>
                        </div>

                        <input type="file" id="photoInput" x-ref="photoInput" name="photo" accept="image/*"
                               class="hidden"
                               @change="handlePhotoChange($event)">

                        {{-- Remove photo option when editing --}}
                        <div x-show="previewPhoto" class="flex items-center gap-2 mt-2">
                            <button type="button" @click="clearPhoto()"
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-500 hover:text-rose-700 transition-colors">
                                <span class="material-symbols-outlined text-[16px]">delete</span>
                                Remove photo
                            </button>
                        </div>
                        <input type="hidden" name="remove_photo" :value="(!previewPhoto && serviceModalMode === 'edit') ? '1' : '0'">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Service Name <span class="text-rose-400">*</span></label>
                            <input type="text" name="name" x-model="serviceForm.name" required
                                   class="block w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm py-3 transition-all"
                                   placeholder="e.g. Regular Wash, Express Clean">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Description</label>
                            <textarea name="description" x-model="serviceForm.description" rows="2"
                                      class="block w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm py-3 transition-all resize-none"
                                      placeholder="Short description of this service..."></textarea>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Base Price (Rp) <span class="text-rose-400">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-gray-400">Rp</span>
                                <input type="number" name="base_price" x-model="serviceForm.base_price" min="0" step="any" required
                                       class="block w-full pl-10 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm py-3 transition-all">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="svc_is_active" name="is_active" value="1" x-model="serviceForm.is_active"
                               class="w-5 h-5 rounded-lg border-gray-200 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        <label for="svc_is_active" class="text-sm font-bold text-gray-700 cursor-pointer select-none">Active (visible to customers)</label>
                    </div>

                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-50">
                        <button type="button" @click="showServiceModal = false"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-black uppercase tracking-widest rounded-xl transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-2.5 bg-[#005bc0] hover:bg-blue-700 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-sm hover:shadow-md">
                            <span x-text="serviceModalMode === 'create' ? 'Create Service' : 'Save Changes'"></span>
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

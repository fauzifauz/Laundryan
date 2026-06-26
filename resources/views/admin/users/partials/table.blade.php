<div class="overflow-x-auto rounded-2xl border border-gray-100">
    <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
        <span>{{ $users->total() }} records found</span>
        <span>Page {{ $users->currentPage() }} / {{ $users->lastPage() }}</span>
    </div>
    <table class="min-w-full divide-y divide-gray-100">
        <thead class="bg-gray-50/70">
            <tr>
                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                    Photo
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_direction' => $currentSortBy === 'name' ? $nextSortDir : 'asc']) }}" class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                        Full Name
                        @if($currentSortBy === 'name')
                            <span class="material-symbols-outlined text-[14px] text-blue-600">{{ $currentSortDir === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                        @else
                            <span class="material-symbols-outlined text-[14px] text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity">arrow_upward</span>
                        @endif
                    </a>
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'email', 'sort_direction' => $currentSortBy === 'email' ? $nextSortDir : 'asc']) }}" class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                        Email / Phone
                        @if($currentSortBy === 'email')
                            <span class="material-symbols-outlined text-[14px] text-blue-600">{{ $currentSortDir === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                        @else
                            <span class="material-symbols-outlined text-[14px] text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity">arrow_upward</span>
                        @endif
                    </a>
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                    Address
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                    Role
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'status', 'sort_direction' => $currentSortBy === 'status' ? $nextSortDir : 'asc']) }}" class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                        Status
                        @if($currentSortBy === 'status')
                            <span class="material-symbols-outlined text-[14px] text-blue-600">{{ $currentSortDir === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                        @else
                            <span class="material-symbols-outlined text-[14px] text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity">arrow_upward</span>
                        @endif
                    </a>
                </th>
                <th scope="col" class="px-6 py-4 text-right text-xs font-black text-gray-400 uppercase tracking-widest">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 bg-white">
            @forelse($users as $user)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <!-- Profile Photo -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($user->photo)
                            <img src="{{ asset('storage/' . $user->photo) }}" class="w-10 h-10 rounded-2xl object-cover border border-gray-100 shadow-sm">
                        @else
                            <div class="w-10 h-10 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 font-black text-sm">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </td>
                    <!-- Full Name -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-black text-gray-900">{{ $user->name }}</div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">NIK: {{ $user->nik ?: '-' }}</div>
                    </td>
                    <!-- Email / Phone -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-600 font-bold">{{ $user->email }}</div>
                        <div class="text-[10px] text-gray-400 font-bold mt-0.5">{{ $user->phone ?: '-' }}</div>
                    </td>
                    <!-- Address -->
                    <td class="px-6 py-4">
                        <div class="text-xs text-gray-600 font-semibold line-clamp-1 max-w-[200px]" title="{{ $user->address }}">
                            {{ $user->address ?: '-' }}
                        </div>
                    </td>
                    <!-- Role -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $roleMap = [
                                'admin' => 'Admin',
                                'karyawan' => 'Staff',
                                'kurir' => 'Courier',
                                'pelanggan' => 'Customer',
                            ];
                        @endphp
                        <span class="px-2.5 py-1 inline-flex text-[9px] leading-5 font-black uppercase tracking-wider rounded-full bg-blue-50 text-blue-600 border border-blue-100">
                            {{ $roleMap[$user->role] ?? ucfirst($user->role) }}
                        </span>
                    </td>
                    <!-- Status -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($user->status === 'active')
                            <span class="px-2.5 py-1 inline-flex text-[9px] leading-5 font-black uppercase tracking-wider rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100">
                                Active
                            </span>
                        @else
                            <span class="px-2.5 py-1 inline-flex text-[9px] leading-5 font-black uppercase tracking-wider rounded-full bg-rose-50 text-rose-600 border border-rose-100">
                                Suspended
                            </span>
                        @endif
                    </td>
                    <!-- Actions -->
                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-black uppercase tracking-wider space-x-1.5">
                        <!-- View Detail Trigger -->
                        <button type="button" @click="fetchDetail({{ $user->id }})" class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200 transition-all hover:scale-105" title="View Details">
                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                        </button>

                        <!-- Edit Profile Trigger -->
                        @if($roleName !== 'pelanggan')
                            <button type="button" @click="openEditModal({{ json_encode($user) }})" class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-600 border border-amber-200 transition-all hover:scale-105" title="Edit Details">
                                <span class="material-symbols-outlined text-[16px]">edit</span>
                            </button>
                        @endif
                        
                        <!-- Toggle Status -->
                        @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="inline-block"
                                  @submit.prevent.stop="
                                      lockUserId = '{{ $user->id }}';
                                      lockUserName = '{{ addslashes($user->name) }}';
                                      lockUserEmail = '{{ addslashes($user->email) }}';
                                      lockUserRole = '{{ $roleMap[$user->role] ?? $user->role }}';
                                      lockUserNewStatus = '{{ $user->status === 'active' ? 'Suspended' : 'Active' }}';
                                      lockUserPhoto = '{{ $user->photo ? asset('storage/' . $user->photo) : '' }}';
                                      lockUserAction = '{{ route('admin.users.update', $user->id) }}';
                                      showLockModal = true;
                                  ">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="{{ $user->status === 'active' ? 'inactive' : 'active' }}">
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-xl {{ $user->status === 'active' ? 'bg-orange-50 hover:bg-orange-100 text-orange-600 border border-orange-200' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-600 border border-emerald-200' }} transition-all hover:scale-105" title="{{ $user->status === 'active' ? 'Suspend Account' : 'Activate Account' }}">
                                    <span class="material-symbols-outlined text-[16px]">{{ $user->status === 'active' ? 'lock' : 'lock_open' }}</span>
                                </button>
                            </form>
                            
                            <!-- Delete -->
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline-block"
                                  @submit.prevent.stop="
                                      deleteUserId = '{{ $user->id }}';
                                      deleteUserName = '{{ addslashes($user->name) }}';
                                      deleteUserEmail = '{{ addslashes($user->email) }}';
                                      deleteUserRole = '{{ $roleMap[$user->role] ?? $user->role }}';
                                      deleteUserPhoto = '{{ $user->photo ? asset('storage/' . $user->photo) : '' }}';
                                      deleteUserAction = '{{ route('admin.users.destroy', $user->id) }}';
                                      showDeleteModal = true;
                                  ">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition-all hover:scale-105" title="Delete User">
                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-xs font-black text-gray-400 italic bg-gray-50/20">
                        No user accounts found for this category.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Table Pagination -->
@if($users->hasPages())
    <div class="mt-4 px-2">
        {{ $users->links() }}
    </div>
@endif

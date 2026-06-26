<section class="space-y-6">
    <header>
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600">
                <span class="material-symbols-outlined text-[22px]">warning</span>
            </div>
            <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">
                {{ __('Delete Account') }}
            </h3>
        </div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
            {{ __('Permanently delete your account and all associated data.') }}
        </p>
    </header>

    <div class="p-6 rounded-3xl bg-rose-50 border border-rose-100 text-rose-700 text-sm">
        <p class="font-semibold leading-relaxed">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </div>

    <div>
        <button
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="inline-flex items-center gap-2 px-6 py-3 bg-rose-600 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-rose-700 hover:shadow-lg hover:shadow-rose-100 active:scale-95 transition-all shadow-sm"
        >
            <span class="material-symbols-outlined text-[16px]">delete_forever</span>
            {{ __('Delete Account') }}
        </button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 md:p-8 space-y-6">
            @csrf
            @method('delete')

            <div>
                <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">
                    {{ __('Are you sure you want to delete your account?') }}
                </h3>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    {{ __('Please enter your password to confirm you would like to permanently delete your account.') }}
                </p>
            </div>

            <!-- Password Input -->
            <div class="relative group" x-data="{ show: false }">
                <label for="password" class="block text-xs font-black uppercase tracking-wider text-gray-400 mb-2 group-focus-within:text-rose-600 transition-colors">
                    {{ __('Password') }}
                </label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-rose-500 transition-colors">lock</span>
                    <input :type="show ? 'text' : 'password'" id="password" name="password" type="password" placeholder="••••••••" class="w-full pl-12 pr-12 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-gray-900 placeholder-gray-400 focus:bg-white focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all font-semibold text-sm shadow-sm" />
                    <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                        <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                    </button>
                </div>
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-50">
                <button type="button" x-on:click="$dispatch('close')" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-gray-700 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gray-50 hover:shadow-sm transition-all shadow-sm">
                    {{ __('Cancel') }}
                </button>

                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-600 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-rose-700 hover:shadow-lg active:scale-95 transition-all shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">delete_forever</span>
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>

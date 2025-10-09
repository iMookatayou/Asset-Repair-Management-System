<section class="space-y-4">
    <header class="mb-1">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Delete Account') }}
        </h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.destroy') }}" class="mt-2 space-y-5">
        @csrf
        @method('delete')

        <div class="space-y-2">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" name="password" type="password"
                          class="mt-1 block w-full" autocomplete="current-password" required />
            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
        </div>

        {{-- ปุ่มเว้นจากขอบล่างเล็กน้อย การ์ดหลักก็ pb-3 แล้ว --}}
        <div class="mt-3 mb-1 flex justify-start">
            <x-danger-button>{{ __('Delete Account') }}</x-danger-button>
        </div>
    </form>
</section>

<div>
    <x-auth-card title="Hoş geldiniz" description="ExportDepo hesabınıza giriş yapın">
        <form wire:submit="login" class="space-y-5">
            <x-input name="email" type="email" label="E-posta" wire:model="email" autocomplete="email" autofocus />
            @error('email')<p class="mt-1 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror

            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Şifre</label>
                    <a href="{{ url('/sifremi-unuttum') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Şifremi unuttum</a>
                </div>
                <x-input name="password" type="password" wire:model="password" autocomplete="current-password" hint="" />
                @error('password')<p class="mt-1 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                <input type="checkbox" wire:model="remember" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:bg-slate-800 dark:border-slate-500">
                Beni hatırla
            </label>

            <x-button type="submit" block wire:loading.attr="disabled">Giriş Yap</x-button>
        </form>

        <x-slot:footer>
            Hesabınız yok mu?
            <a href="{{ url('/kayit') }}" class="font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Üye olun</a>
        </x-slot:footer>
    </x-auth-card>
</div>

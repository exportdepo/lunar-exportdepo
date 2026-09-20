<div>
    <x-auth-card icon="sifre" title="Yeni Şifre Belirle" description="Hesabınız için yeni bir şifre oluşturun">
        @if ($resetSuccess)
            <div class="text-center">
                <div class="text-5xl" aria-hidden="true">✓</div>
                <h2 class="mt-4 text-xl font-bold text-emerald-900 dark:text-emerald-300">Şifreniz güncellendi</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Yeni şifrenizle giriş yapabilirsiniz.</p>
                <x-button href="{{ url('/giris') }}" class="mt-6">Giriş Yap</x-button>
            </div>
        @else
            <form wire:submit="resetPassword" class="space-y-5">
                <x-input name="email" type="email" label="E-posta" wire:model="email" autocomplete="email" />
                @error('email')<p class="mt-1 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror

                <x-input name="password" type="password" label="Yeni Şifre" wire:model="password" autocomplete="new-password" autofocus hint="En az 8 karakter" />
                @error('password')<p class="mt-1 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror

                <x-input name="password_confirmation" type="password" label="Yeni Şifre Tekrar" wire:model="password_confirmation" autocomplete="new-password" />

                <x-button type="submit" block wire:loading.attr="disabled">Şifreyi Güncelle</x-button>
            </form>
        @endif
    </x-auth-card>
</div>

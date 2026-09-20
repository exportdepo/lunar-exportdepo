<div>
    <x-auth-card icon="sifre" title="Şifremi Unuttum" description="E-posta adresinize şifre sıfırlama bağlantısı göndereceğiz">
        @if ($emailSent)
            <div class="text-center">
                <div class="text-5xl" aria-hidden="true">📬</div>
                <h2 class="mt-4 text-xl font-bold text-emerald-900 dark:text-emerald-300">E-posta gönderildi</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Şifre sıfırlama bağlantısı <strong>{{ $email }}</strong> adresine gönderildi. Lütfen gelen kutunuzu kontrol edin.</p>
                <x-button href="{{ url('/giris') }}" class="mt-6">Giriş sayfasına dön</x-button>
            </div>
        @else
            <form wire:submit="sendResetLink" class="space-y-5">
                <x-input name="email" type="email" label="E-posta" wire:model="email" autocomplete="email" autofocus />
                @error('email')<p class="mt-1 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror

                <x-button type="submit" block wire:loading.attr="disabled">Sıfırlama Bağlantısı Gönder</x-button>
            </form>
        @endif

        <x-slot:footer>
            <a href="{{ url('/giris') }}" class="font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">← Giriş sayfasına dön</a>
        </x-slot:footer>
    </x-auth-card>
</div>

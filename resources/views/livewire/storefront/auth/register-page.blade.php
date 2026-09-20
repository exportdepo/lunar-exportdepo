<div>
    <x-auth-card title="Hesap Oluşturun" description="Lunar Shop&apos;a üye olarak siparişlerinizi takip edin">
        <form wire:submit="register" class="space-y-5">
            <x-input name="name" type="text" label="Ad Soyad" wire:model="name" autocomplete="name" autofocus />
            @error('name')<p class="mt-1 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror

            <x-input name="email" type="email" label="E-posta" wire:model="email" autocomplete="email" />
            @error('email')<p class="mt-1 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror

            <x-input name="phone" type="tel" label="Telefon" hint="Opsiyonel" wire:model="phone" autocomplete="tel" />
            @error('phone')<p class="mt-1 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror

            <x-input name="password" type="password" label="Şifre" wire:model="password" autocomplete="new-password" hint="En az 8 karakter" />
            @error('password')<p class="mt-1 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror

            <x-input name="password_confirmation" type="password" label="Şifre Tekrar" wire:model="password_confirmation" autocomplete="new-password" />

            <x-button type="submit" block wire:loading.attr="disabled">Üye Ol</x-button>
        </form>

        <x-slot:footer>
            Zaten hesabınız var mı?
            <a href="{{ url('/giris') }}" class="font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Giriş yapın</a>
        </x-slot:footer>
    </x-auth-card>
</div>

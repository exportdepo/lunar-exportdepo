<div>
    <section class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <div class="text-center mb-8">
            <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white text-3xl shadow-md">🌙</span>
            <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">Hesap Oluşturun</h1>
            <p class="mt-2 text-sm text-slate-500">Lunar Shop'a üye olarak siparişlerinizi takip edin</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <form wire:submit="register" class="space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Ad Soyad</label>
                    <input id="name" type="text" wire:model="name" autocomplete="name" autofocus class="mt-2 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">E-posta</label>
                    <input id="email" type="email" wire:model="email" autocomplete="email" class="mt-2 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700">Telefon <span class="text-slate-400">(opsiyonel)</span></label>
                    <input id="phone" type="tel" wire:model="phone" autocomplete="tel" class="mt-2 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    @error('phone')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Şifre</label>
                    <input id="password" type="password" wire:model="password" autocomplete="new-password" class="mt-2 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-slate-500">En az 8 karakter</p>
                    @error('password')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Şifre Tekrar</label>
                    <input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" class="mt-2 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700 transition-colors">
                    Üye Ol
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-slate-600">
            Zaten hesabınız var mı?
            <a href="{{ url('/giris') }}" class="font-semibold text-indigo-600 hover:text-indigo-800">Giriş yapın</a>
        </p>
    </section>
</div>

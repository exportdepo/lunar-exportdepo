<div>
    <section class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <div class="text-center mb-8">
            <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white text-3xl shadow-md">🌙</span>
            <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">Hoş geldiniz</h1>
            <p class="mt-2 text-sm text-slate-500">Lunar Shop hesabınıza giriş yapın</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <form wire:submit="login" class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">E-posta</label>
                    <input id="email" type="email" wire:model="email" autocomplete="email" autofocus class="mt-2 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium text-slate-700">Şifre</label>
                        <a href="{{ url('/sifremi-unuttum') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Şifremi unuttum</a>
                    </div>
                    <input id="password" type="password" wire:model="password" autocomplete="current-password" class="mt-2 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    @error('password')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" wire:model="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Beni hatırla
                </label>
                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700 transition-colors">
                    Giriş Yap
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-slate-600">
            Hesabınız yok mu?
            <a href="{{ url('/kayit') }}" class="font-semibold text-indigo-600 hover:text-indigo-800">Üye olun</a>
        </p>
    </section>
</div>

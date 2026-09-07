<div>
    <section class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <div class="text-center mb-8">
            <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white text-3xl shadow-md">🔑</span>
            <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">Şifremi Unuttum</h1>
            <p class="mt-2 text-sm text-slate-500">E-posta adresinize şifre sıfırlama bağlantısı göndereceğiz</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            @if ($emailSent)
                <div class="text-center">
                    <div class="text-5xl">📬</div>
                    <h2 class="mt-4 text-xl font-bold text-emerald-900">E-posta gönderildi</h2>
                    <p class="mt-2 text-sm text-slate-600">Şifre sıfırlama bağlantısı <strong>{{ $email }}</strong> adresine gönderildi. Lütfen gelen kutunuzu kontrol edin.</p>
                    <a href="{{ url('/giris') }}" class="mt-6 inline-flex rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">Giriş sayfasına dön</a>
                </div>
            @else
                <form wire:submit="sendResetLink" class="space-y-5">
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">E-posta</label>
                        <input id="email" type="email" wire:model="email" autocomplete="email" autofocus class="mt-2 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700 transition-colors">
                        Sıfırlama Bağlantısı Gönder
                    </button>
                </form>
            @endif
        </div>

        <p class="mt-6 text-center text-sm text-slate-600">
            <a href="{{ url('/giris') }}" class="font-semibold text-indigo-600 hover:text-indigo-800">← Giriş sayfasına dön</a>
        </p>
    </section>
</div>

<div>
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if ($orderReference)
            <div class="mx-auto max-w-2xl rounded-2xl border border-emerald-200 bg-emerald-50 p-10 text-center">
                <div class="text-5xl">✓</div>
                <h1 class="mt-4 text-3xl font-extrabold text-emerald-900">Siparişiniz alındı</h1>
                <p class="mt-3 text-emerald-800">Sipariş numaranız: <strong>{{ $orderReference }}</strong></p>
                <p class="mt-2 text-sm text-emerald-700">Ödeme yöntemi: Manuel ödeme</p>
                <a href="{{ url('/') }}" class="mt-8 inline-flex rounded-lg bg-emerald-700 px-5 py-3 font-semibold text-white hover:bg-emerald-800">Alışverişe dön</a>
            </div>
        @elseif (empty($summary))
            <div class="mx-auto max-w-2xl rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                <h1 class="text-2xl font-bold text-slate-900">Sepetiniz boş</h1>
                <p class="mt-2 text-slate-500">Checkout için önce ürün eklemelisiniz.</p>
                <a href="{{ url('/') }}" class="mt-6 inline-flex rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">Ürünlere git</a>
            </div>
        @else
            <div class="mb-8">
                <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Son adım</p>
                <h1 class="mt-2 text-4xl font-extrabold tracking-tight text-slate-900">Siparişi tamamla</h1>
            </div>
            <form wire:submit="placeOrder" class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-xl font-bold text-slate-900">Teslimat adresi</h2>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div><label for="firstName" class="block text-sm font-medium text-slate-700">Ad</label><input id="firstName" wire:model="firstName" class="mt-2 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">@error('firstName')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
                        <div><label for="lastName" class="block text-sm font-medium text-slate-700">Soyad</label><input id="lastName" wire:model="lastName" class="mt-2 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">@error('lastName')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
                        <div><label for="email" class="block text-sm font-medium text-slate-700">E-posta</label><input id="email" type="email" wire:model="email" class="mt-2 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">@error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
                        <div><label for="phone" class="block text-sm font-medium text-slate-700">Telefon</label><input id="phone" wire:model="phone" class="mt-2 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">@error('phone')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
                        <div class="sm:col-span-2"><label for="lineOne" class="block text-sm font-medium text-slate-700">Adres</label><input id="lineOne" wire:model="lineOne" class="mt-2 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">@error('lineOne')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
                        <div><label for="city" class="block text-sm font-medium text-slate-700">Şehir</label><input id="city" wire:model="city" class="mt-2 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">@error('city')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
                        <div><label for="state" class="block text-sm font-medium text-slate-700">İlçe</label><input id="state" wire:model="state" class="mt-2 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"></div>
                        <div><label for="postcode" class="block text-sm font-medium text-slate-700">Posta kodu</label><input id="postcode" wire:model="postcode" class="mt-2 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">@error('postcode')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
                    </div>
                    <h2 class="mt-10 text-xl font-bold text-slate-900">Ödeme</h2>
                    <label class="mt-4 flex items-start gap-3 rounded-xl border border-indigo-200 bg-indigo-50 p-4">
                        <input type="radio" wire:model="paymentMethod" value="offline" class="mt-1 text-indigo-600 focus:ring-indigo-500">
                        <span><strong class="block text-slate-900">Manuel ödeme</strong><span class="text-sm text-slate-600">Sipariş oluşturulur; ödeme mağaza tarafından ayrıca teyit edilir.</span></span>
                    </label>
                    @if ($stripeEnabled)
                        <label class="mt-3 flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-4">
                            <input type="radio" wire:model="paymentMethod" value="stripe" class="mt-1 text-indigo-600 focus:ring-indigo-500">
                            <span><strong class="block text-slate-900">Kart ile ödeme</strong><span class="text-sm text-slate-600">Güvenli Stripe Checkout sayfasına yönlendirilirsiniz.</span></span>
                        </label>
                    @endif
                    @error('paymentMethod')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <aside class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900">Sipariş özeti</h2>
                    <div class="mt-5 flex items-center justify-between text-sm text-slate-600"><span>{{ $summary['count'] }} ürün</span><span>TRY</span></div>
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4"><span class="font-semibold text-slate-700">Toplam</span><span class="text-2xl font-extrabold text-slate-900">{{ number_format($summary['total'] / 100, 2, ',', '.') }} ₺</span></div>
                    <button type="submit" class="mt-6 inline-flex w-full justify-center rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">Siparişi tamamla</button>
                </aside>
            </form>
        @endif
    </section>
</div>

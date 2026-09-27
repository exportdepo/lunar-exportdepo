{{--
    ExportDepo "İletişim" kurumsal sayfası.

    İki bloktan oluşur: iletişim bilgisi kartları (demo veri) ve çalışan
    mesaj formu. Form ContactPage::submitMessage() üzerinden işlenir:
    Türkçe doğrulama, gizli "website" honeypot alanı ve WithRateLimiting
    (dakikada en fazla 5 deneme) ile korunur; mesaj `contact_messages`
    tablosuna kaydedilir.
--}}
<div>
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <div class="anim-fade-up mb-10 max-w-3xl">
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">İletişim</h1>
            <p class="mt-3 text-base text-slate-500 dark:text-slate-400">Soru, öneri ve talepleriniz için bize ulaşın; en kısa sürede dönüş yapalım.</p>
        </div>

        <div class="grid grid-cols-1 gap-10 lg:grid-cols-5">
            {{-- Bilgi kartları --}}
            <div class="space-y-4 lg:col-span-2">
                <div class="anim-fade-up flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-lg text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400" aria-hidden="true">📍</span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Adres</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">ExportDepo Merkez<br>Levent, İstanbul</p>
                    </div>
                </div>
                <div class="anim-fade-up flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-lg text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400" aria-hidden="true">✉️</span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">E-posta</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">destek@exportdepo.test</p>
                    </div>
                </div>
                <div class="anim-fade-up flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-lg text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400" aria-hidden="true">📞</span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Telefon</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">0850 000 00 00</p>
                    </div>
                </div>
                <div class="anim-fade-up flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-lg text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400" aria-hidden="true">🕘</span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Çalışma Saatleri</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Hafta içi 09.00 – 18.00<br>Hafta sonu: çevrimiçi destek</p>
                    </div>
                </div>
            </div>

            {{-- Mesaj formu --}}
            <div class="anim-fade-up rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 lg:col-span-3 dark:border-slate-800 dark:bg-slate-900">
                @if ($sent)
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <span class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-2xl text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400" aria-hidden="true">✅</span>
                        <h2 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">Mesajınız bize ulaştı!</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">En kısa sürede e-posta adresinize dönüş yapacağız.</p>
                        <x-button variant="outline" size="sm" class="mt-6" wire:click="$set('sent', false)">Yeni mesaj gönder</x-button>
                    </div>
                @else
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Bize yazın</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Formu doldurun; mesajınız ekibimize iletilsin.</p>
                    {{-- FORM_PLACEHOLDER --}}
                    <form wire:submit="submitMessage" class="mt-6 space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <x-input name="name" label="Adınız" wire:model="name" placeholder="Adınız Soyadınız" required />
                            <x-input name="email" label="E-posta" type="email" wire:model="email" placeholder="ornek@site.com" autocomplete="email" required />
                        </div>
                        <x-input name="subject" label="Konu" wire:model="subject" placeholder="Mesajınızın konusu" required />
                        <x-textarea name="message" label="Mesajınız" wire:model="message" rows="6" placeholder="Bize iletmek istediğiniz mesajı yazın..." required />

                        {{-- Honeypot: insanlar görmez, botlar doldurur --}}
                        <input type="text" wire:model="website" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" class="hidden">

                        <x-button type="submit" wire:loading.attr="disabled">Mesajı Gönder</x-button>
                    </form>
                @endif
            </div>
        </div>
    </section>
</div>

{{--
    ExportDepo e-bülten abonelik formu (Livewire).

    Footer'ın bülten bloğunda kullanılır; koyu slate-900 zemin üzerine tasarlandığından
    açık temalı `x-input` yerine yerinde koyu zemin sınıfları kullanılır.

    - Doğrulama: Türkçe mesajlarla e-posta kontrolü (`@error` ile gösterilir).
    - Spam koruması: gizli "website" honeypot alanı (bot doldurursa kayıt yapılmaz,
      bota başarı gösterilir) + `WithRateLimiting` (dakikada en fazla 5 deneme).
    - Aynı e-posta ikinci kez gönderilirse kayıt çoğaltılmaz; kullanıcıya
      "zaten abone" bilgisi verilir.
--}}

<div class="w-full">
    @if (filled($successMessage))
        <p role="status" class="flex items-center gap-2 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ $successMessage }}
        </p>
    @else
        <form wire:submit="subscribe" class="flex w-full max-w-md gap-2">
            <label for="newsletter-email" class="sr-only">E-posta adresi</label>
            <input id="newsletter-email"
                   type="email"
                   wire:model="email"
                   placeholder="E-posta adresiniz"
                   autocomplete="email"
                   required
                   @error('email') aria-invalid="true" aria-describedby="newsletter-email-error" @enderror
                   class="block h-11 w-full min-w-0 flex-1 rounded-lg border border-slate-700 bg-slate-800 px-4 text-sm text-white shadow-sm transition-colors placeholder:text-slate-500 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/30">
            <x-button type="submit" size="md" class="shrink-0" wire:loading.attr="disabled">Abone Ol</x-button>

            {{-- Honeypot: insanlar görmez, botlar doldurur --}}
            <input type="text" wire:model="website" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" class="hidden">
        </form>

        @error('email')
            <p id="newsletter-email-error" class="mt-2 flex items-center gap-1 text-xs font-medium text-rose-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                </svg>
                {{ $message }}
            </p>
        @enderror
    @endif
</div>

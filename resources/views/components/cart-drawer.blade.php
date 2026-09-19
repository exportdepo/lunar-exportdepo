{{--
    ExportDepo sepet çekmecesi (drawer) kabuğu.

    Açılıp kapanması Alpine `$store.cartDrawer` store'u üzerinden yönetilir:
    header'daki sepet düğmesi `show()/toggle()` çağırır; backdrop tıklaması,
    Escape tuşu ve kapatma düğmesi `hide()` çağırır. Açıkken sayfa kaydırması
    kilitlenir (overflow-hidden).

    İçerik iki parça halinde bileşene aktarılır:
    - `$slot`  : kaydırılabilir ana alan (sepet satırları vb.)
    - `$footer`: sabit alt alan (toplamlar + CTA'lar, opsiyonel)

    Kullanım:
        <x-cart-drawer title="Sepetim (3 ürün)">
            ... satırlar ...

            <x-slot:footer>
                ... toplamlar ve butonlar ...
            </x-slot:footer>
        </x-cart-drawer>
--}}
@props([
    'title' => 'Sepetim',
])

<div id="cart-drawer" x-data
     x-show="$store.cartDrawer.open"
     x-cloak
     @keydown.escape.window="$store.cartDrawer.hide()"
     x-effect="document.documentElement.classList.toggle('overflow-hidden', $store.cartDrawer.open)"
     class="fixed inset-0 z-50"
     role="dialog"
     aria-modal="true"
     :aria-hidden="!$store.cartDrawer.open">

    {{-- Karartma katmanı: tıklanınca çekmece kapanır --}}
    <div x-show="$store.cartDrawer.open"
         x-transition.opacity.duration.300ms
         @click="$store.cartDrawer.hide()"
         class="absolute inset-0 bg-slate-900/50"
         aria-hidden="true"></div>

    {{-- Sağdan kayan panel --}}
    <div x-show="$store.cartDrawer.open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="absolute right-0 top-0 flex h-full w-full max-w-md flex-col bg-white shadow-2xl dark:bg-slate-900">

        {{-- Başlık --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ $title }}</h2>
            <button type="button"
                    @click="$store.cartDrawer.hide()"
                    aria-label="Sepeti kapat"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Kaydırılabilir içerik --}}
        <div class="flex-1 overflow-y-auto px-5 py-4">
            {{ $slot }}
        </div>

        {{-- Sabit alt alan (opsiyonel) --}}
        @isset($footer)
            <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>

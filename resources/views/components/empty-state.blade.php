{{--
    Boş durum (empty state) bileşeni.

    Kullanım:
        <x-empty-state title="Henüz ürün yok" description="Demo veri yüklenmedi." icon="📦" />
        <x-empty-state title="..." description="..." icon="🔍">
            <x-button href="/" size="sm">Alışverişe Başla</x-button>
        </x-empty-state>
--}}
@props([
    'title' => 'Kayıt bulunamadı',
    'description' => null,
    'icon' => '📦',
])

<div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
    <div class="text-5xl" aria-hidden="true">{{ $icon }}</div>
    <h3 class="mt-4 text-xl font-semibold text-slate-800 dark:text-slate-100">{{ $title }}</h3>

    @if (filled($description))
        <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $description }}</p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="mt-6 flex justify-center">{{ $slot }}</div>
    @endif
</div>

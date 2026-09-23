{{--
    Auth (giriş/kayıt/şifre) sayfaları için ortak kart iskeleti.

    Üstte simge rozeti + başlık + alt açıklama, ortada form kartı,
    altta yardımcı bağlantı satırı sunar. Formun kendisi slot ile aktarılır.

    Kullanım:
        <x-auth-card icon="giris" title="Hoş geldiniz" description="...">
            <form>...</form>
            <x-slot:footer>...</x-slot:footer>
        </x-auth-card>

    icon: giris | kayit | sifre (diğer değerlerde varsayılan ay simgesi)
--}}
@props([
    'icon' => 'giris',
    'title' => '',
    'description' => null,
])

@php
    $iconMap = [
        'giris' => '🌙',
        'kayit' => '🌙',
        'sifre' => '🔑',
    ];
    $iconGlyph = $iconMap[$icon] ?? '🌙';
@endphp

<section class="mx-auto max-w-md px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
    <div class="mb-8 text-center">
        <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-linear-to-br from-indigo-500 to-purple-600 text-3xl text-white shadow-md">{{ $iconGlyph }}</span>
        <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h1>
        @if (filled($description))
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
        @endif
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
        {{ $slot }}
    </div>

    @isset($footer)
        <p class="mt-6 text-center text-sm text-slate-600 dark:text-slate-400">
            {{ $footer }}
        </p>
    @endisset
</section>

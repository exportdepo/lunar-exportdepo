@php
    $active = $count > 0;
@endphp

<a href="{{ url('/istek-listesi') }}"
   title="İstek Listem"
   aria-label="İstek Listem"
   class="relative inline-flex items-center gap-2 px-3 py-2 rounded-lg transition-colors
          {{ $active ? 'text-rose-600 hover:bg-rose-50' : 'text-slate-600 hover:bg-slate-100 hover:text-rose-600' }}">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 10-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
    </svg>
    <span class="hidden sm:inline">İstek Listem</span>
    @if ($count > 0)
        <span class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-5 h-5 px-1 text-xs font-bold text-white bg-rose-500 rounded-full">{{ $count }}</span>
    @endif
</a>
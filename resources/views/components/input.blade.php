{{--
    ExportDepo atomik form girdisi bileşeni.

    Kullanım:
        <x-input name="email" type="email" label="E-posta" placeholder="ornek@site.com" />
        <x-input name="firstName" label="Ad" wire:model="firstName" required />

    - `error` verilmezse Laravel/Livewire `$errors` torbasından otomatik okunur.
    - `value` verilmezse klasik formlar için `old($name)` kullanılır; Livewire
      sayfalarında değer `wire:model` üzerinden taşındığı için gerek yoktur.
    - Ek tüm nitelikler ($attributes) <input> elemanına aktarılır (wire:model,
      autocomplete, autofocus, min, max vb.).
--}}
@props([
    'name' => null,
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'error' => null,
    'id' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $inputId = $id ?? $name;
    $resolvedValue = $value ?? (filled($name) ? old($name) : null);
    $resolvedError = $error ?? (($errors ?? null) && filled($name) && $errors->has($name) ? $errors->first($name) : null);
    $hasError = filled($resolvedError);

    $stateClasses = $hasError
        ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/30 dark:border-rose-500/60 dark:focus:border-rose-400'
        : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500/30 dark:border-slate-600 dark:focus:border-indigo-400 dark:focus:ring-indigo-400/30';
@endphp

<div class="w-full">
    @if (filled($label))
        <label for="{{ $inputId }}" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
            {{ $label }}

            @if ($required)
                <span class="text-rose-500" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <input id="{{ $inputId }}"
           @if (filled($name)) name="{{ $name }}" @endif
           type="{{ $type }}"
           @if ($resolvedValue !== null && $resolvedValue !== '') value="{{ $resolvedValue }}" @endif
           @if (filled($placeholder)) placeholder="{{ $placeholder }}" @endif
           @if ($required) required @endif
           @if ($disabled) disabled @endif
           @if ($hasError) aria-invalid="true" aria-describedby="{{ $inputId }}-error" @endif
           {{ $attributes->merge(['class' => 'block w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition-colors placeholder:text-slate-400 focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:disabled:bg-slate-900 dark:disabled:text-slate-500 '.$stateClasses]) }}>

    @if ($hasError)
        <p id="{{ $inputId }}-error" class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-600 dark:text-rose-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
            </svg>
            {{ $resolvedError }}
        </p>
    @elseif (filled($hint))
        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
</div>

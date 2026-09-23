@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex items-center w-full px-4 py-3 rounded-2xl text-base font-bold text-[#155d49] bg-[#f0f8f5] border border-emerald-200/80 shadow-xs transition duration-150 ease-in-out'
            : 'flex items-center w-full px-4 py-3 rounded-2xl text-base font-semibold text-gray-700 hover:text-gray-900 hover:bg-gray-100 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

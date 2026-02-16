@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-amber-500  text-md font-medium leading-5 text-cyan-200  focus:outline-none focus:border-pink-600 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-md font-medium leading-5 text-cyan-50 hover:text-cyan-300 hover:border-cyan-100  focus:outline-none focus:text-cyan-300 focus:border-cyan-100 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

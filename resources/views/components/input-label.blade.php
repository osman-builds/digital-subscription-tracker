@props(['value', 'for' => null])

<label for="{{ $for ?? $attributes->get('for') }}" {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700']) }}>
    {{ $value ?? $slot }}
</label>

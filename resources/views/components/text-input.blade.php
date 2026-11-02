@props(['disabled' => false, 'id' => null])

<input id="{{ $id ?? $attributes->get('id') }}" aria-label="{{ $attributes->get('aria-label') ?? 'Form input' }}" @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) }}>

@props(['id', 'label', 'placeholder' => null])

<div class="w-full max-w-sm" data-admin-filter-input>
    <label for="{{ $id }}" class="sr-only">{{ $label }}</label>
    <div class="relative">
        <span
            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-stone-400">
            <flux:icon.magnifying-glass class="size-4" />
        </span>
        <input id="{{ $id }}" type="text" placeholder="{{ $placeholder ?? $label }}"
            {{ $attributes->merge([
                'class' =>
                    'w-full rounded-md border border-stone-300 bg-white py-2 pl-10 pr-3 text-sm text-stone-700 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]',
            ]) }} />
    </div>
</div>

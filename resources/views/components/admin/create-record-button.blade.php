<button type="button"
    {{ $attributes->merge(['class' => 'inline-flex items-center rounded-md bg-[#d9755b] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2']) }}>
    {{ $slot->isEmpty() ? __('general.buttons.create_new') : $slot }}
</button>

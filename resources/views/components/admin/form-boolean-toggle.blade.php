@props(['label', 'model', 'value' => false, 'trueLabel', 'falseLabel'])

<div data-admin-field="boolean-toggle">
    <label class="mb-2 block text-sm font-semibold text-stone-800">
        {{ $label }}
    </label>

    <div class="inline-flex items-center gap-1 rounded-full border border-stone-300 bg-white p-1">
        <button type="button" wire:click="$set('{{ $model }}', true)"
            @class([
                'rounded-full px-3 py-1 text-xs font-semibold transition-colors',
                'bg-[#d9755b] text-white' => $value,
                'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' => !$value,
            ])>
            {{ $trueLabel }}
        </button>

        <button type="button" wire:click="$set('{{ $model }}', false)"
            @class([
                'rounded-full px-3 py-1 text-xs font-semibold transition-colors',
                'bg-[#d9755b] text-white' => !$value,
                'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' => $value,
            ])>
            {{ $falseLabel }}
        </button>
    </div>
</div>

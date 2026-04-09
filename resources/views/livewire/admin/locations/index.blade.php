<div>
    {{-- Type tabs --}}
    <div class="mb-6 flex gap-2">
        @foreach ($typeLabels as $key => $label)
            <flux:button :variant="$type === $key ? 'primary' : 'ghost'"
                wire:click="setType('{{ $key }}')" data-type="{{ $key }}">
                {{ $label }}
            </flux:button>
        @endforeach
    </div>

    <div
        class="overflow-x-auto rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <table class="min-w-full text-sm">
            <thead class="border-b border-zinc-200 dark:border-zinc-700">
                <tr class="text-left text-zinc-600 dark:text-zinc-300">
                    <th class="px-4 py-3">{{ __('Código') }}</th>
                    <th class="px-4 py-3">{{ __('Nombre') }}</th>
                    <th class="px-4 py-3">{{ __('Propiedades') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($locations as $location)
                    <tr wire:key="location-{{ $location->id }}"
                        data-location-id="{{ $location->id }}"
                        class="border-b border-zinc-100 dark:border-zinc-700">
                        <td class="px-4 py-3">{{ $location->code }}</td>
                        <td class="px-4 py-3">{{ $location->name }}</td>
                        <td class="px-4 py-3">{{ $location->properties_count }}</td>
                        <td class="px-4 py-3">
                            <flux:button variant="ghost" size="sm"
                                :href="route('admin.locations.show', $location)">
                                {{ __('Ver') }}
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-zinc-400">
                            {{ __('No hay registros.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $locations->links() }}
    </div>
</div>

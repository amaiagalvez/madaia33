<div>
    {{-- Filters --}}
    <div class="mb-6 flex flex-wrap gap-3" data-section="filters">
        <flux:select wire:model.live="filterStatus" data-filter="status">
            <flux:select.option value="active">{{ __('Propietarias activas') }}</flux:select.option>
            <flux:select.option value="inactive">{{ __('Sin asignación activa') }}
            </flux:select.option>
            <flux:select.option value="all">{{ __('Todas') }}</flux:select.option>
        </flux:select>

        <flux:select wire:model.live="filterPortal" data-filter="portal">
            <flux:select.option value="">{{ __('Todos los portales') }}</flux:select.option>
            @foreach ($portals as $portal)
                <flux:select.option :value="$portal->id">{{ $portal->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="filterGarage" data-filter="garage">
            <flux:select.option value="">{{ __('Todos los garajes') }}</flux:select.option>
            @foreach ($garages as $garage)
                <flux:select.option :value="$garage->id">{{ $garage->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="filterStorage" data-filter="storage">
            <flux:select.option value="">{{ __('Todos los trasteros') }}</flux:select.option>
            @foreach ($storages as $storage)
                <flux:select.option :value="$storage->id">{{ $storage->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div
        class="overflow-x-auto rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <table class="min-w-full text-sm">
            <thead class="border-b border-zinc-200 dark:border-zinc-700">
                <tr class="text-left text-zinc-600 dark:text-zinc-300">
                    <th class="px-4 py-3">{{ __('Copropietaria 1') }}</th>
                    <th class="px-4 py-3">{{ __('Copropietaria 2') }}</th>
                    <th class="px-4 py-3">{{ __('Portales') }}</th>
                    <th class="px-4 py-3">{{ __('Garajes') }}</th>
                    <th class="px-4 py-3">{{ __('Trasteros') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($owners as $owner)
                    <tr wire:key="owner-{{ $owner->id }}" data-owner-id="{{ $owner->id }}"
                        class="border-b border-zinc-100 dark:border-zinc-700">
                        <td class="px-4 py-3">{{ $owner->coprop1_name }}</td>
                        <td class="px-4 py-3">{{ $owner->coprop2_name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $portalAssignments = $owner->assignments->filter(
                                    fn($a) => $a->property->location->type === 'portal',
                                );
                            @endphp
                            @foreach ($portalAssignments as $a)
                                <span
                                    class="{{ $a->isActive() ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $a->property->location->code }} {{ $a->property->name }}
                                </span><br>
                            @endforeach
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $garageAssignments = $owner->assignments->filter(
                                    fn($a) => $a->property->location->type === 'garage',
                                );
                            @endphp
                            @foreach ($garageAssignments as $a)
                                <span
                                    class="{{ $a->isActive() ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $a->property->location->code }} {{ $a->property->name }}
                                </span><br>
                            @endforeach
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $storageAssignments = $owner->assignments->filter(
                                    fn($a) => $a->property->location->type === 'storage',
                                );
                            @endphp
                            @foreach ($storageAssignments as $a)
                                <span
                                    class="{{ $a->isActive() ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $a->property->location->code }} {{ $a->property->name }}
                                </span><br>
                            @endforeach
                        </td>
                        <td class="px-4 py-3">
                            <flux:button variant="ghost" size="sm"
                                :href="route('admin.owners.show', $owner)">
                                {{ __('Ver') }}
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-zinc-400">
                            {{ __('No hay propietarias.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $owners->links() }}
    </div>
</div>

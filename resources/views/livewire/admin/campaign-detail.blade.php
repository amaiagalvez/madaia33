<div class="space-y-6" data-campaign-detail-page>
    @if (session()->has('message'))
        <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="rounded-md bg-amber-50 p-4 text-sm text-amber-800">
            {{ session('warning') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
        <x-admin.stat-card :label="__('campaigns.admin.metrics.total')" :value="$metrics['total']" data-campaign-metric="total" />
        <x-admin.stat-card :label="__('campaigns.admin.metrics.opens')" :value="$metrics['opens']" data-campaign-metric="opens" />
        <x-admin.stat-card :label="__('campaigns.admin.metrics.clicks')" :value="$metrics['clicks']" data-campaign-metric="clicks" />
        <x-admin.stat-card :label="__('campaigns.admin.metrics.downloads')" :value="$metrics['downloads']" data-campaign-metric="downloads" />
        <x-admin.stat-card :label="__('campaigns.admin.metrics.failures')" :value="$metrics['failures']" data-campaign-metric="failures" />
    </div>

    <div class="rounded-xl border border-[#edd2c7] bg-[#edd2c7]/25 p-4">
        <p class="text-sm font-semibold text-[#793d3d]">{{ __('campaigns.admin.subject') }}</p>
        <p class="mt-1 text-sm text-stone-700">
            {{ $campaign->subject_eu ?? ($campaign->subject_es ?? '—') }}</p>
        <p class="mt-3 text-xs text-stone-600">{{ __('campaigns.admin.channel') }}:
            {{ __('campaigns.admin.channels.' . $campaign->channel) }}</p>

        @if ($canResendToUnopened)
            <div class="mt-3 flex items-center gap-3">
                <x-admin.action-link-confirm wire:click="resendToUnopened"
                    title="{{ __('campaigns.admin.actions.resend_unopened') }}" state="neutral"
                    data-campaign-resend-unopened>
                    <flux:icon.arrow-path class="size-4" />
                </x-admin.action-link-confirm>

                <span class="text-xs text-stone-600">
                    {{ __('campaigns.admin.unopened_count', ['count' => $unopenedRecipientsCount]) }}
                </span>
            </div>
        @elseif ($allOpenedNotice)
            <div class="mt-3 rounded-md bg-amber-50 px-3 py-2 text-xs text-amber-700"
                data-campaign-all-opened-notice>
                {{ __('campaigns.admin.messages.all_opened') }}
            </div>
        @endif
    </div>

    <x-admin.panel-table table-class="min-w-full divide-y divide-gray-200"
        data-campaign-recipient-detail-table>
        <thead class="bg-gray-50">
            <tr>
                <x-admin.table-header-cell>{{ __('campaigns.admin.recipient_name') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('campaigns.admin.contact') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('campaigns.admin.status') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('campaigns.admin.opened') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('campaigns.admin.clicks_count') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('campaigns.admin.downloads_count') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell>{{ __('campaigns.admin.last_activity') }}</x-admin.table-header-cell>
                <x-admin.table-header-cell class="relative">
                    <span class="sr-only">{{ __('campaigns.admin.events') }}</span>
                </x-admin.table-header-cell>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse ($recipientRows as $row)
                <tr wire:key="campaign-detail-row-{{ $row['id'] }}">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $row['name'] }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $row['contact'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $row['status'] === 'failed' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                            {{ $row['status_label'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $row['opened'] ? __('campaigns.admin.yes') : __('campaigns.admin.no') }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $row['clicks'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $row['downloads'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $row['last_activity']?->format('d/m/Y H:i') ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <x-admin.action-link-confirm
                            wire:click="toggleRecipientDetails({{ $row['id'] }})"
                            title="{{ __('campaigns.admin.events') }}" state="neutral">
                            <flux:icon.chevron-down class="size-4" />
                        </x-admin.action-link-confirm>
                    </td>
                </tr>

                @if ($expandedRecipientId === $row['id'])
                    <tr wire:key="campaign-detail-events-{{ $row['id'] }}" class="bg-stone-50">
                        <td colspan="8" class="px-6 py-4">
                            <div class="space-y-3 rounded-lg border border-stone-200 bg-white p-4"
                                data-campaign-event-list>
                                <h4 class="text-sm font-semibold text-stone-900">
                                    {{ __('campaigns.admin.event_details') }}</h4>

                                @forelse ($row['events'] as $event)
                                    <div
                                        class="rounded-md border border-stone-200 px-3 py-2 text-sm text-stone-700">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span
                                                class="font-semibold text-[#793d3d]">{{ $event['type_label'] }}</span>
                                            <span
                                                class="text-xs text-stone-500">{{ $event['created_at']?->format('d/m/Y H:i') ?? '—' }}</span>
                                            @if ($event['ip_address'])
                                                <span class="text-xs text-stone-500">IP:
                                                    {{ $event['ip_address'] }}</span>
                                            @endif
                                        </div>

                                        @if ($event['url'])
                                            <p class="mt-1 break-all text-xs text-stone-600">
                                                {{ $event['url'] }}</p>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-sm text-stone-500">
                                        {{ __('campaigns.admin.no_events') }}</p>
                                @endforelse
                            </div>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">
                        {{ __('campaigns.admin.empty_recipients') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.panel-table>
</div>

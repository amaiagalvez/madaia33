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

        <div class="rounded-lg border border-[#edd2c7] bg-white p-6 shadow-sm"
            data-campaign-metric="opens">
            <div class="flex h-full flex-col gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">
                        {{ __('campaigns.admin.metrics.opens') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $openRateSummary }}</p>
                </div>

                @if ($canResendToUnopened)
                    <div class="space-y-4">
                        <button type="button" wire:click="confirmResendToUnopened"
                            class="inline-flex items-center gap-2 rounded-full border border-[#d9755b] bg-[#edd2c7]/40 px-3 py-1.5 text-xs font-semibold text-[#793d3d] transition hover:bg-[#edd2c7]"
                            data-campaign-resend-unopened>
                            <flux:icon.arrow-path class="size-4" />
                            <span>{{ __('campaigns.admin.actions.resend_unopened') }}</span>
                        </button>

                        <p class="text-xs text-stone-600">
                            {{ __('campaigns.admin.unopened_count', ['count' => $unopenedRecipientsCount]) }}
                        </p>
                    </div>
                @elseif ($allOpenedNotice)
                    <p class="rounded-md bg-green-50 px-3 py-2 text-xs text-green-700"
                        data-campaign-all-opened-notice>
                        {{ __('campaigns.admin.messages.all_opened') }}
                    </p>
                @endif
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
            data-campaign-metric="clicks">
            <div class="flex h-full flex-col gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">
                        {{ __('campaigns.admin.metrics.clicks') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $metrics['clicks'] }}</p>
                </div>

                @if ($clickBreakdown !== [])
                    <div class="space-y-2" data-campaign-click-breakdown>
                        <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">
                            {{ __('campaigns.admin.click_breakdown') }}</p>
                        <ul class="space-y-2">
                            @foreach ($clickBreakdown as $item)
                                <li
                                    class="flex items-start justify-between gap-3 rounded-md bg-stone-50 px-3 py-2">
                                    <span
                                        class="break-all text-xs text-stone-700">{{ $item['label'] }}</span>
                                    <span
                                        class="shrink-0 rounded-full bg-[#edd2c7]/40 px-2 py-0.5 text-xs font-semibold text-[#793d3d]">
                                        {{ $item['count'] }}×
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
            data-campaign-metric="downloads">
            <div class="flex h-full flex-col gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">
                        {{ __('campaigns.admin.metrics.downloads') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $metrics['downloads'] }}
                    </p>
                </div>

                @if ($downloadBreakdown !== [])
                    <div class="space-y-2" data-campaign-download-breakdown>
                        <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">
                            {{ __('campaigns.admin.download_breakdown') }}</p>
                        <ul class="space-y-2">
                            @foreach ($downloadBreakdown as $item)
                                <li
                                    class="flex items-start justify-between gap-3 rounded-md bg-stone-50 px-3 py-2">
                                    <span
                                        class="break-all text-xs text-stone-700">{{ $item['label'] }}</span>
                                    <span
                                        class="shrink-0 rounded-full bg-[#edd2c7]/40 px-2 py-0.5 text-xs font-semibold text-[#793d3d]">
                                        {{ $item['count'] }}×
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <x-admin.stat-card :label="__('campaigns.admin.metrics.failures')" :value="$metrics['failures']" data-campaign-metric="failures" />
    </div>

    @if ($showResendModal)
        <dialog open
            class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4"
            aria-labelledby="campaign-resend-modal-title">
            <div class="mx-4 w-full max-w-sm space-y-4 rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-100">
                        <flux:icon.arrow-path class="size-5 text-green-600" />
                    </div>
                    <div>
                        <h3 id="campaign-resend-modal-title"
                            class="text-base font-semibold text-gray-900">
                            {{ __('campaigns.admin.actions.resend_unopened') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('campaigns.admin.confirm_resend_unopened') }}
                        </p>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="cancelResendToUnopened"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b]">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button type="button" wire:click="doResendToUnopened"
                        class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        {{ __('general.buttons.confirm') }}
                    </button>
                </div>
            </div>
        </dialog>
    @endif

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
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        <div class="flex items-center gap-2">
                            @if ($row['owner_edit_url'])
                                <a href="{{ $row['owner_edit_url'] }}" target="_blank"
                                    rel="noopener noreferrer"
                                    title="{{ __('admin.owners.edit_owner') }}"
                                    data-campaign-owner-edit-{{ $row['owner_id'] }}
                                    class="inline-flex items-center rounded-full border border-transparent p-2 text-[#d9755b] transition-colors hover:border-brand-300/40 hover:bg-brand-100/40 hover:text-[#793d3d]">
                                    <flux:icon.pencil-square class="size-4" />
                                    <span
                                        class="sr-only">{{ __('admin.owners.edit_owner') }}</span>
                                </a>
                            @endif

                            <span>{{ $row['name'] }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <div class="flex flex-wrap items-center gap-2">
                            <span>{{ $row['contact'] }}</span>

                            @if ($row['whatsapp_url'])
                                <a href="{{ $row['whatsapp_url'] }}" target="_blank"
                                    rel="noopener noreferrer"
                                    wire:click="markWhatsappSent({{ $row['id'] }})"
                                    data-campaign-whatsapp-send-{{ $row['id'] }}
                                    class="inline-flex items-center gap-1 rounded-full border border-[#25D366]/40 bg-[#25D366]/10 px-2.5 py-1 text-xs font-semibold text-[#0f5132] transition hover:bg-[#25D366]/20">
                                    <flux:icon.chat-bubble-left-right class="size-3.5" />
                                    <span>{{ __('campaigns.admin.actions.send_whatsapp') }}</span>
                                </a>

                                @if ($row['whatsapp_sent'])
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700"
                                        data-campaign-whatsapp-sent-{{ $row['id'] }}>
                                        <flux:icon.check class="size-3.5" />
                                        <span>{{ __('campaigns.admin.whatsapp.sent_badge') }}</span>
                                    </span>
                                @endif
                            @endif
                        </div>
                    </td>
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
                                                <span class="text-xs text-stone-500">
                                                    {{ __('campaigns.admin.ip_address') }}:
                                                    {{ $event['ip_address'] }}
                                                </span>
                                            @endif
                                        </div>

                                        @if ($event['document_label'])
                                            <p class="mt-1 text-xs text-stone-600">
                                                {{ __('campaigns.admin.document_name') }}:
                                                <span
                                                    class="font-medium text-stone-700">{{ $event['document_label'] }}</span>
                                            </p>
                                        @endif

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

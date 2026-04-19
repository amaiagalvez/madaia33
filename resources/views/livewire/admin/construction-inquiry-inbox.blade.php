<div>
    <div class="mb-4 grid gap-3 md:grid-cols-[minmax(0,18rem)_auto] md:items-end">
        <label class="block text-sm font-medium text-gray-700">
            {{ __('admin.construction_inquiries.filter_construction') }}
            <select wire:model.live="constructionFilter"
                class="mt-1 block w-full min-h-11 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:border-brand-600 focus:outline-none focus:ring-1 focus:ring-brand-600"
                data-construction-inquiry-filter>
                <option value="all">{{ __('admin.construction_inquiries.all_constructions') }}
                </option>
                @foreach ($constructionOptions as $constructionId => $constructionTitle)
                    <option value="{{ $constructionId }}">{{ $constructionTitle }}</option>
                @endforeach
            </select>
        </label>
    </div>

    <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50" data-admin-table-header>
                    <tr>
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                            {{ __('admin.construction_inquiries.from') }}</th>
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                            {{ __('admin.construction_inquiries.construction') }}</th>
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                            {{ __('admin.construction_inquiries.received') }}</th>
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                            {{ __('admin.construction_inquiries.replied') }}</th>
                        <th
                            class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-stone-500">
                            {{ __('general.buttons.edit') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($inquiries as $inquiry)
                        <tr class="cursor-pointer transition-colors {{ $inquiry->is_read ? 'bg-white' : 'bg-[#edd2c7]/20' }}"
                            wire:click="openInquiry({{ $inquiry->id }})"
                            data-construction-inquiry-row="{{ $inquiry->id }}">
                            <td class="px-4 py-4 align-top text-sm text-stone-800">
                                <div class="font-medium">{{ $inquiry->name }}</div>
                                <div class="text-xs text-stone-500">{{ $inquiry->email }}</div>
                                <div class="mt-1 text-sm text-stone-600">{{ $inquiry->subject }}
                                </div>
                            </td>
                            <td class="px-4 py-4 align-top text-sm text-stone-700">
                                {{ $inquiry->construction->title }}</td>
                            <td class="px-4 py-4 align-top text-sm text-stone-700">
                                {{ $inquiry->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-4 align-top text-sm text-stone-700">
                                @if ($inquiry->replied_at)
                                    <span
                                        class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        {{ __('admin.construction_inquiries.replied_at', ['date' => $inquiry->replied_at->format('d/m/Y H:i')]) }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                        {{ __('admin.construction_inquiries.not_replied') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 align-top text-right">
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                        class="inline-flex min-h-10 items-center justify-center rounded-full border border-stone-200 bg-white px-3 py-2 text-xs font-semibold text-stone-600 transition-colors hover:border-brand-300 hover:text-brand-700"
                                        wire:click.stop="toggleRead({{ $inquiry->id }})"
                                        title="{{ $inquiry->is_read ? __('admin.construction_inquiries.mark_unread') : __('admin.construction_inquiries.mark_read') }}">
                                        {{ $inquiry->is_read ? __('admin.construction_inquiries.mark_unread') : __('admin.construction_inquiries.mark_read') }}
                                    </button>
                                    <button type="button"
                                        class="inline-flex min-h-10 items-center justify-center rounded-full border border-[#edd2c7] bg-white px-3 py-2 text-xs font-semibold text-[#793d3d] transition-colors hover:border-[#d9755b] hover:text-[#5f2f2f]"
                                        wire:click.stop="openReplyModal({{ $inquiry->id }})"
                                        data-admin-action-link="confirm">
                                        {{ __('admin.construction_inquiries.reply_button') }}
                                    </button>
                                </div>
                            </td>
                        </tr>

                        @if ($openInquiryId === $inquiry->id)
                            <tr>
                                <td colspan="5" class="bg-stone-50 px-4 py-5">
                                    <div
                                        class="grid gap-4 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                                        <div>
                                            <h3 class="text-sm font-semibold text-stone-900">
                                                {{ $inquiry->subject }}</h3>
                                            <p
                                                class="mt-3 whitespace-pre-line text-sm leading-6 text-stone-700">
                                                {{ $inquiry->message }}</p>
                                        </div>
                                        <div
                                            class="rounded-2xl border border-stone-200 bg-white p-4 text-sm text-stone-600">
                                            <dl class="space-y-3">
                                                <div>
                                                    <dt class="font-medium text-stone-700">
                                                        {{ __('admin.construction_inquiries.construction') }}
                                                    </dt>
                                                    <dd class="mt-1">
                                                        {{ $inquiry->construction->title }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="font-medium text-stone-700">
                                                        {{ __('admin.construction_inquiries.from') }}
                                                    </dt>
                                                    <dd class="mt-1">{{ $inquiry->name }} ·
                                                        {{ $inquiry->email }}</dd>
                                                </div>
                                                @if ($inquiry->reply)
                                                    <div>
                                                        <dt class="font-medium text-stone-700">
                                                            {{ __('admin.construction_inquiries.reply_body') }}
                                                        </dt>
                                                        <dd class="mt-1 whitespace-pre-line">
                                                            {{ $inquiry->reply }}</dd>
                                                    </div>
                                                @endif
                                            </dl>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                                {{ __('admin.construction_inquiries.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-stone-200 px-4 py-3">
            {{ $inquiries->links() }}
        </div>
    </div>

    @if ($showReplyModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4">
            <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-stone-900">
                    {{ __('admin.construction_inquiries.compose_reply') }}</h3>
                <textarea wire:model="replyBody" rows="8"
                    class="mt-4 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:border-brand-600 focus:outline-none focus:ring-1 focus:ring-brand-600"
                    placeholder="{{ __('admin.construction_inquiries.reply_placeholder') }}"></textarea>
                @error('replyBody')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <div class="mt-4 flex justify-end gap-3" data-admin-form-footer-actions>
                    <button type="button"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition-colors hover:bg-stone-50"
                        wire:click="cancelReply">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button type="button"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#793d3d] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#5f2f2f]"
                        wire:click="sendReply" wire:loading.attr="disabled" wire:target="sendReply">
                        {{ __('admin.construction_inquiries.send_reply') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

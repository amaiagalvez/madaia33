<?php

use App\Models\Setting;
use Livewire\Component;

new class extends Component {
    public string $privacyContentEu = '';

    public string $privacyContentEs = '';

    public string $legalNoticeContentEu = '';

    public string $legalNoticeContentEs = '';

    public bool $saved = false;

    /**
     * @return array<string, string>
     */
    protected function rules(): array
    {
        return [
            'privacyContentEu' => 'nullable|string',
            'privacyContentEs' => 'nullable|string',
            'legalNoticeContentEu' => 'nullable|string',
            'legalNoticeContentEs' => 'nullable|string',
        ];
    }

    public function mount(): void
    {
        $keys = ['legal_page_privacy_policy_eu', 'legal_page_privacy_policy_es', 'legal_page_legal_notice_eu', 'legal_page_legal_notice_es'];

        $settings = Setting::whereIn('key', $keys)
            ->get(['key', 'value'])
            ->pluck('value', 'key');

        $this->privacyContentEu = (string) ($settings['legal_page_privacy_policy_eu'] ?? '');
        $this->privacyContentEs = (string) ($settings['legal_page_privacy_policy_es'] ?? '');
        $this->legalNoticeContentEu = (string) ($settings['legal_page_legal_notice_eu'] ?? '');
        $this->legalNoticeContentEs = (string) ($settings['legal_page_legal_notice_es'] ?? '');
    }

    public function save(): void
    {
        $this->validate();

        $timestamp = now();

        Setting::upsert([['key' => 'legal_page_privacy_policy_eu', 'value' => $this->privacyContentEu, 'created_at' => $timestamp, 'updated_at' => $timestamp], ['key' => 'legal_page_privacy_policy_es', 'value' => $this->privacyContentEs, 'created_at' => $timestamp, 'updated_at' => $timestamp], ['key' => 'legal_page_legal_notice_eu', 'value' => $this->legalNoticeContentEu, 'created_at' => $timestamp, 'updated_at' => $timestamp], ['key' => 'legal_page_legal_notice_es', 'value' => $this->legalNoticeContentEs, 'created_at' => $timestamp, 'updated_at' => $timestamp]], ['key'], ['value', 'updated_at']);

        $this->saved = true;
    }
};
?>

<div>
    @if ($saved)
        <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-800">
            {{ __('general.messages.saved') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-8">

        {{-- Privacy Policy --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold text-gray-800">
                {{ __('admin.legal.privacy_policy') }}</h2>

            <div class="space-y-4">
                <div>
                    <label for="privacyContentEu" class="block text-sm font-medium text-gray-700">
                        {{ __('admin.legal.content_eu') }}
                    </label>
                    <textarea id="privacyContentEu" wire:model="privacyContentEu" rows="8"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
                    @error('privacyContentEu')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="privacyContentEs" class="block text-sm font-medium text-gray-700">
                        {{ __('admin.legal.content_es') }}
                    </label>
                    <textarea id="privacyContentEs" wire:model="privacyContentEs" rows="8"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
                    @error('privacyContentEs')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Legal Notice --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold text-gray-800">
                {{ __('admin.legal.legal_notice') }}</h2>

            <div class="space-y-4">
                <div>
                    <label for="legalNoticeContentEu"
                        class="block text-sm font-medium text-gray-700">
                        {{ __('admin.legal.content_eu') }}
                    </label>
                    <textarea id="legalNoticeContentEu" wire:model="legalNoticeContentEu" rows="8"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
                    @error('legalNoticeContentEu')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="legalNoticeContentEs"
                        class="block text-sm font-medium text-gray-700">
                        {{ __('admin.legal.content_es') }}
                    </label>
                    <textarea id="legalNoticeContentEs" wire:model="legalNoticeContentEs" rows="8"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
                    @error('legalNoticeContentEs')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div>
            <button type="submit"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ __('general.buttons.save') }}
            </button>
        </div>
    </form>
</div>

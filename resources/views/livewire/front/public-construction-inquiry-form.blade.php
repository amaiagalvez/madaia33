<div data-construction-inquiry-form>
    @if ($statusType === 'error')
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
            role="alert" aria-live="polite">
            {{ $statusMessage }}
        </div>
    @endif

    <form wire:submit="submit" class="{{ $statusType !== null ? 'mt-4' : '' }} space-y-4" novalidate>
        <div>
            <label for="construction-inquiry-message"
                class="mb-1 block text-sm font-medium text-gray-700">
                {{ __('constructions.inquiry.message') }}
                <span aria-hidden="true" class="text-red-500">*</span>
            </label>
            <textarea id="construction-inquiry-message" wire:model="message" rows="5"
                class="block w-full rounded-md border bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:outline-none focus:ring-1 {{ $errors->has('message') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-gray-300 focus:border-brand-600 focus:ring-brand-600' }}"
                @if ($errors->has('message')) aria-invalid="true" aria-describedby="construction-inquiry-message-error" @endif></textarea>
            @error('message')
                <p id="construction-inquiry-message-error" class="mt-1 text-sm text-red-600">
                    {{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end gap-3">
            <button type="submit"
                class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#793d3d] disabled:opacity-50"
                wire:loading.attr="disabled" wire:target="submit" data-construction-inquiry-submit>
                <span wire:loading.remove
                    wire:target="submit">{{ __('constructions.inquiry.send') }}</span>
                <span wire:loading wire:target="submit">{{ __('general.sending') }}</span>
            </button>
        </div>
    </form>
</div>

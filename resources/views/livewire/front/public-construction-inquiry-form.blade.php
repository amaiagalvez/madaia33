<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm"
    data-construction-inquiry-form>
    <div class="space-y-2">
        <h2 class="text-xl font-semibold tracking-tight text-stone-900">
            {{ __('constructions.inquiry.title') }}</h2>
        <p class="text-sm leading-6 text-stone-600">{{ __('constructions.inquiry.subtitle') }}</p>
    </div>

    @if ($statusType === 'success')
        <div class="mt-5 rounded-2xl border border-green-300 bg-linear-to-r from-green-50 to-white px-4 py-4 text-sm text-green-900"
            role="alert" aria-live="polite">
            {{ $statusMessage }}
        </div>
    @endif

    <form wire:submit="submit" class="mt-6 space-y-4" novalidate>

        <div>
            <label for="construction-inquiry-subject"
                class="mb-1 block text-sm font-medium text-gray-700">
                {{ __('constructions.inquiry.subject') }}
            </label>
            <input id="construction-inquiry-subject" type="text" wire:model="subject"
                class="block w-full min-h-11 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]"
                @if ($errors->has('subject')) aria-invalid="true" aria-describedby="construction-inquiry-subject-error" @endif>
            @error('subject')
                <p id="construction-inquiry-subject-error" class="mt-1 text-sm text-red-600">
                    {{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="construction-inquiry-message"
                class="mb-1 block text-sm font-medium text-gray-700">
                {{ __('constructions.inquiry.message') }}
            </label>
            <textarea id="construction-inquiry-message" wire:model="message" rows="5"
                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-colors focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]"
                @if ($errors->has('message')) aria-invalid="true" aria-describedby="construction-inquiry-message-error" @endif></textarea>
            @error('message')
                <p id="construction-inquiry-message-error" class="mt-1 text-sm text-red-600">
                    {{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
            class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-[#793d3d] px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#5f2f2f] focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 disabled:opacity-50"
            wire:loading.attr="disabled" wire:target="submit" data-construction-inquiry-submit>
            <span wire:loading.remove>{{ __('constructions.inquiry.send') }}</span>
            <span wire:loading wire:target="submit">{{ __('general.sending') }}</span>
        </button>
    </form>
</div>

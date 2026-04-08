<x-layouts::public :title="__($titleKey)">
    @php
        $descriptionKey =
            $pageSlug === 'privacy-policy'
                ? 'general.footer.privacy_policy_description'
                : 'general.footer.legal_notice_description';
    @endphp

    @push('meta')
        <meta name="description" content="{{ __($descriptionKey) }}">
    @endpush

    <div class="max-w-prose mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14"
        data-legal-page="{{ $pageSlug }}">
        <x-public-page-header hero="legal" variant="legal" :show-legal-chips="true" :title="__($titleKey)"
            :description="__($descriptionKey)" />

        <div class="hero-frame px-5 py-5 sm:px-6">
            <div class="space-y-4 text-base leading-relaxed text-gray-700">
                @if ($content)
                    {!! nl2br(e($content)) !!}
                @else
                    <p class="italic text-gray-500">{{ __($titleKey) }}</p>
                @endif
            </div>

            <div class="mt-6 border-t border-gray-200 pt-4 text-xs leading-relaxed text-gray-500">
                <p>{{ __('general.footer.privacy_policy_description') }}</p>
                <p class="mt-2">{{ __('general.footer.legal_notice_description') }}</p>
            </div>
        </div>
    </div>
</x-layouts::public>

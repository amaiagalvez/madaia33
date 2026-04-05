<x-layouts::public :title="__('general.footer.legal_notice')">
    @push('meta')
        <meta name="description" content="{{ __('general.footer.legal_notice') }} - {{ config('app.name') }}">
    @endpush

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('general.footer.legal_notice') }}</h1>

        <div class="prose prose-gray max-w-none text-gray-700">
            @if ($content)
                {!! nl2br(e($content)) !!}
            @else
                <p class="text-gray-500 italic">{{ __('general.footer.legal_notice') }}</p>
            @endif
        </div>
    </div>
</x-layouts::public>

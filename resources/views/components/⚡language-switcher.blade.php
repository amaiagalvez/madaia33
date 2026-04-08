<?php

use App\SupportedLocales;
use Livewire\Component;
use Illuminate\Support\Facades\App;

new class extends Component {
    public string $currentLocale = SupportedLocales::DEFAULT;

    /** @var array<string, string> */
    public array $localeUrls = [];

    public function mount(): void
    {
        $this->currentLocale = SupportedLocales::normalize(App::getLocale());

        $currentRoute = request()->route();
        $routeName = (string) $currentRoute?->getName();
        $baseName = SupportedLocales::baseRouteName($routeName);

        foreach (SupportedLocales::all() as $locale) {
            try {
                $this->localeUrls[$locale] = route(SupportedLocales::routeName($baseName, $locale));
            } catch (\Throwable) {
                $this->localeUrls[$locale] = route(SupportedLocales::routeName('home', $locale));
            }
        }
    }
};
?>

<div class="flex items-center gap-2">
    @foreach (SupportedLocales::all() as $locale)
        <a href="{{ $this->localeUrls[$locale] }}" data-language-option="{{ $locale }}"
            class="cursor-pointer rounded px-2 py-1 text-sm font-medium transition-colors {{ $currentLocale === $locale ? 'bg-gray-800 text-white' : 'text-gray-600 hover:text-gray-900' }}"
            aria-label="{{ __('general.language.' . $locale) }}"
            @if ($currentLocale === $locale) aria-current="true" @endif>
            {{ SupportedLocales::switcherLabel($locale) }}
        </a>
        @if (!$loop->last)
            <span class="text-gray-400" aria-hidden="true">|</span>
        @endif
    @endforeach
</div>

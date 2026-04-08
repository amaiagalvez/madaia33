<?php

use App\SupportedLocales;
use Livewire\Component;
use Illuminate\Support\Facades\App;

new class extends Component {
    public string $currentLocale = SupportedLocales::DEFAULT;

    public function mount(): void
    {
        $this->currentLocale = SupportedLocales::normalize(App::getLocale());
    }

    public function switchLocale(string $locale): void
    {
        if (!SupportedLocales::isSupported($locale)) {
            return;
        }

        session(['locale' => $locale]);

        $this->redirect(request()->header('Referer', '/'), navigate: false);
    }
};
?>

<div class="flex items-center gap-2">
    @foreach (SupportedLocales::all() as $locale)
        <button wire:click="switchLocale('{{ $locale }}')"
            data-language-option="{{ $locale }}"
            class="cursor-pointer rounded px-2 py-1 text-sm font-medium transition-colors {{ $currentLocale === $locale ? 'bg-gray-800 text-white' : 'text-gray-600 hover:text-gray-900' }}"
            aria-label="{{ __('general.language.' . $locale) }}"
            @if ($currentLocale === $locale) aria-current="true" @endif>
            {{ SupportedLocales::switcherLabel($locale) }}
        </button>
        @if (!$loop->last)
            <span class="text-gray-400" aria-hidden="true">|</span>
        @endif
    @endforeach
</div>

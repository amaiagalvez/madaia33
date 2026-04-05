<?php

use Livewire\Component;
use Illuminate\Support\Facades\App;

new class extends Component {
    public string $currentLocale = 'eu';

    public function mount(): void
    {
        $this->currentLocale = App::getLocale();
    }

    public function switchLocale(string $locale): void
    {
        if (!in_array($locale, ['eu', 'es'])) {
            return;
        }

        session(['locale' => $locale]);

        $this->redirect(request()->header('Referer', '/'), navigate: false);
    }
};
?>

<div class="flex items-center gap-2">
    <button wire:click="switchLocale('eu')"
        class="text-sm font-medium px-2 py-1 rounded transition-colors {{ $currentLocale === 'eu' ? 'bg-gray-800 text-white' : 'text-gray-600 hover:text-gray-900' }}"
        aria-label="{{ __('general.language.eu') }}" @if ($currentLocale === 'eu') aria-current="true" @endif>
        EU
    </button>
    <span class="text-gray-400" aria-hidden="true">|</span>
    <button wire:click="switchLocale('es')"
        class="text-sm font-medium px-2 py-1 rounded transition-colors {{ $currentLocale === 'es' ? 'bg-gray-800 text-white' : 'text-gray-600 hover:text-gray-900' }}"
        aria-label="{{ __('general.language.es') }}" @if ($currentLocale === 'es') aria-current="true" @endif>
        ES
    </button>
</div>

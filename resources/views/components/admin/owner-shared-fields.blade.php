@props([
    'mode' => 'http',
    'owner' => null,
    'coprop1NameModel' => 'editCoprop1Name',
    'coprop1SurnameModel' => 'editCoprop1Surname',
    'coprop1DniModel' => 'editCoprop1Dni',
    'coprop1PhoneModel' => 'editCoprop1Phone',
    'coprop1EmailModel' => 'editCoprop1Email',
    'languageModel' => 'editLanguage',
    'coprop2NameModel' => 'editCoprop2Name',
    'coprop2SurnameModel' => 'editCoprop2Surname',
    'coprop2DniModel' => 'editCoprop2Dni',
    'coprop2PhoneModel' => 'editCoprop2Phone',
    'coprop2EmailModel' => 'editCoprop2Email',
])

@php
    $isWireMode = $mode === 'wire';
    $inputClass =
        'mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]';
@endphp

<div class="grid gap-4 lg:grid-cols-2" data-owner-shared-form="true"
    data-owner-shared-form-mode="{{ $mode }}">
    <div class="rounded-lg border border-zinc-200 p-4">
        <h3 class="mb-3 text-sm font-semibold text-zinc-800">{{ __('admin.owners.columns.coprop1') }}
        </h3>

        <div class="grid gap-3">
            @if ($isWireMode)
                <flux:field>
                    <flux:label>{{ __('admin.owners.form.coprop1_name') }} <span class="text-red-600"
                            aria-hidden="true">*</span></flux:label>
                    <flux:input wire:model="{{ $coprop1NameModel }}" />
                    <flux:error name="{{ $coprop1NameModel }}" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('admin.owners.form.coprop1_surname') }}</flux:label>
                    <flux:input wire:model="{{ $coprop1SurnameModel }}" />
                    <flux:error name="{{ $coprop1SurnameModel }}" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('admin.owners.form.coprop1_dni') }}</flux:label>
                    <flux:input wire:model="{{ $coprop1DniModel }}" />
                    <flux:error name="{{ $coprop1DniModel }}" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('admin.owners.form.coprop1_phone') }}</flux:label>
                    <flux:input wire:model="{{ $coprop1PhoneModel }}" />
                    <flux:error name="{{ $coprop1PhoneModel }}" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('admin.owners.form.coprop1_email') }} <span
                            class="text-red-600" aria-hidden="true">*</span></flux:label>
                    <flux:input wire:model="{{ $coprop1EmailModel }}" type="email" />
                    <flux:error name="{{ $coprop1EmailModel }}" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('admin.owners.form.language') }} <span class="text-red-600"
                            aria-hidden="true">*</span></flux:label>
                    <flux:select wire:model="{{ $languageModel }}">
                        <flux:select.option value="eu">{{ __('general.language.eu') }}
                        </flux:select.option>
                        <flux:select.option value="es">{{ __('general.language.es') }}
                        </flux:select.option>
                    </flux:select>
                    <flux:error name="{{ $languageModel }}" />
                </flux:field>
            @else
                <label class="text-sm font-medium text-gray-700">
                    {{ __('admin.owners.form.coprop1_name') }} <span class="text-red-600"
                        aria-hidden="true">*</span>
                    <input type="text" name="coprop1_name"
                        value="{{ old('coprop1_name', $owner?->coprop1_name) }}"
                        class="{{ $inputClass }}">
                </label>
                <label class="text-sm font-medium text-gray-700">
                    {{ __('admin.owners.form.coprop1_surname') }}
                    <input type="text" name="coprop1_surname"
                        value="{{ old('coprop1_surname', $owner?->coprop1_surname) }}"
                        class="{{ $inputClass }}">
                </label>
                <label class="text-sm font-medium text-gray-700">
                    {{ __('admin.owners.form.coprop1_dni') }}
                    <input type="text" name="coprop1_dni"
                        value="{{ old('coprop1_dni', $owner?->coprop1_dni) }}"
                        class="{{ $inputClass }}">
                </label>
                <label class="text-sm font-medium text-gray-700">
                    {{ __('admin.owners.form.coprop1_phone') }}
                    <input type="text" name="coprop1_phone"
                        value="{{ old('coprop1_phone', $owner?->coprop1_phone) }}"
                        class="{{ $inputClass }}">
                </label>
                <label class="text-sm font-medium text-gray-700">
                    {{ __('admin.owners.form.coprop1_email') }} <span class="text-red-600"
                        aria-hidden="true">*</span>
                    <input type="email" name="coprop1_email"
                        value="{{ old('coprop1_email', $owner?->coprop1_email) }}"
                        class="{{ $inputClass }}">
                </label>
                <label class="text-sm font-medium text-gray-700">
                    {{ __('admin.owners.form.language') }} <span class="text-red-600"
                        aria-hidden="true">*</span>
                    <select name="language" class="{{ $inputClass }}">
                        <option value="eu" @selected(old('language', $owner?->language) === 'eu')>
                            {{ __('general.language.eu') }}</option>
                        <option value="es" @selected(old('language', $owner?->language) === 'es')>
                            {{ __('general.language.es') }}</option>
                    </select>
                </label>
            @endif
        </div>
    </div>

    <div class="rounded-lg border border-zinc-200 p-4">
        <h3 class="mb-3 text-sm font-semibold text-zinc-800">
            {{ __('admin.owners.columns.coprop2') }}</h3>

        <div class="grid gap-3">
            @if ($isWireMode)
                <flux:field>
                    <flux:label>{{ __('admin.owners.form.coprop2_name') }}</flux:label>
                    <flux:input wire:model="{{ $coprop2NameModel }}" />
                    <flux:error name="{{ $coprop2NameModel }}" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('admin.owners.form.coprop2_surname') }}</flux:label>
                    <flux:input wire:model="{{ $coprop2SurnameModel }}" />
                    <flux:error name="{{ $coprop2SurnameModel }}" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('admin.owners.form.coprop2_dni') }}</flux:label>
                    <flux:input wire:model="{{ $coprop2DniModel }}" />
                    <flux:error name="{{ $coprop2DniModel }}" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('admin.owners.form.coprop2_phone') }}</flux:label>
                    <flux:input wire:model="{{ $coprop2PhoneModel }}" />
                    <flux:error name="{{ $coprop2PhoneModel }}" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('admin.owners.form.coprop2_email') }}</flux:label>
                    <flux:input wire:model="{{ $coprop2EmailModel }}" type="email" />
                    <flux:error name="{{ $coprop2EmailModel }}" />
                </flux:field>
            @else
                <label class="text-sm font-medium text-gray-700">
                    {{ __('admin.owners.form.coprop2_name') }}
                    <input type="text" name="coprop2_name"
                        value="{{ old('coprop2_name', $owner?->coprop2_name) }}"
                        class="{{ $inputClass }}">
                </label>
                <label class="text-sm font-medium text-gray-700">
                    {{ __('admin.owners.form.coprop2_surname') }}
                    <input type="text" name="coprop2_surname"
                        value="{{ old('coprop2_surname', $owner?->coprop2_surname) }}"
                        class="{{ $inputClass }}">
                </label>
                <label class="text-sm font-medium text-gray-700">
                    {{ __('admin.owners.form.coprop2_dni') }}
                    <input type="text" name="coprop2_dni"
                        value="{{ old('coprop2_dni', $owner?->coprop2_dni) }}"
                        class="{{ $inputClass }}">
                </label>
                <label class="text-sm font-medium text-gray-700">
                    {{ __('admin.owners.form.coprop2_phone') }}
                    <input type="text" name="coprop2_phone"
                        value="{{ old('coprop2_phone', $owner?->coprop2_phone) }}"
                        class="{{ $inputClass }}">
                </label>
                <label class="text-sm font-medium text-gray-700">
                    {{ __('admin.owners.form.coprop2_email') }}
                    <input type="email" name="coprop2_email"
                        value="{{ old('coprop2_email', $owner?->coprop2_email) }}"
                        class="{{ $inputClass }}">
                </label>
            @endif
        </div>
    </div>
</div>

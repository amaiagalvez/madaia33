{{-- Admin email --}}
<div>
    <label for="adminEmail" class="block text-sm font-medium text-stone-700">
        {{ __('admin.settings_form.admin_email') }}
    </label>
    <input id="adminEmail" type="email" wire:model="adminEmail"
        class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
    @error('adminEmail')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<x-admin.bilingual-rich-text-tabs :title="__('admin.settings_form.legal_text')" :locale-configs="$this->localeConfigsFor('legalCheckboxText', 'admin.settings_form.legal_text')" />

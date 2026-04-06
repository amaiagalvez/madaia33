<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use App\Validations\AdminSettingsValidation;

class AdminSettings extends Component
{
    public string $adminEmail = '';

    public string $recaptchaSiteKey = '';

    public string $recaptchaSecretKey = '';

    public string $legalCheckboxTextEu = '';

    public string $legalCheckboxTextEs = '';

    public string $legalUrl = '';

    public bool $saved = false;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return AdminSettingsValidation::rules();
    }

    /**
     * @return array<string, mixed>
     */
    protected function messages(): array
    {
        return AdminSettingsValidation::messages();
    }

    public function mount(): void
    {
        $keys = [
            'admin_email',
            'recaptcha_site_key',
            'recaptcha_secret_key',
            'legal_checkbox_text_eu',
            'legal_checkbox_text_es',
            'legal_url',
        ];

        $settings = Setting::whereIn('key', $keys)->pluck('value', 'key');

        $this->adminEmail = $settings['admin_email'] ?? '';
        $this->recaptchaSiteKey = $settings['recaptcha_site_key'] ?? '';
        $this->recaptchaSecretKey = $settings['recaptcha_secret_key'] ?? '';
        $this->legalCheckboxTextEu = $settings['legal_checkbox_text_eu'] ?? '';
        $this->legalCheckboxTextEs = $settings['legal_checkbox_text_es'] ?? '';
        $this->legalUrl = $settings['legal_url'] ?? '';
    }

    public function save(): void
    {
        $this->validate();

        $timestamp = now();

        Setting::upsert([
            ['key' => 'admin_email', 'value' => $this->adminEmail, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['key' => 'recaptcha_site_key', 'value' => $this->recaptchaSiteKey, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['key' => 'recaptcha_secret_key', 'value' => $this->recaptchaSecretKey, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['key' => 'legal_checkbox_text_eu', 'value' => $this->legalCheckboxTextEu, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['key' => 'legal_checkbox_text_es', 'value' => $this->legalCheckboxTextEs, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['key' => 'legal_url', 'value' => $this->legalUrl, 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ], ['key'], ['value', 'updated_at']);

        $this->saved = true;
    }

    public function render(): View
    {
        return view('livewire.admin-settings');
    }
}

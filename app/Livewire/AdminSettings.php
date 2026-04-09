<?php

namespace App\Livewire;

use App\Concerns\BuildsLocaleFieldConfigs;
use App\Mail\TestEmail;
use App\Models\Setting;
use App\Support\ConfiguredMailSettings;
use Illuminate\Contracts\View\View;
use App\Validations\AdminSettingsValidation;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class AdminSettings extends Component
{
    use BuildsLocaleFieldConfigs;

    public string $activeSection = '';

    /** @var array<int, string> */
    public array $availableSections = [];

    public string $adminEmail = '';

    public string $recaptchaSiteKey = '';

    public string $recaptchaSecretKey = '';

    public string $legalCheckboxTextEu = '';

    public string $legalCheckboxTextEs = '';

    public string $privacyContentEu = '';

    public string $privacyContentEs = '';

    public string $historyTextEu = '';

    public string $historyTextEs = '';

    public string $legalNoticeContentEu = '';

    public string $legalNoticeContentEs = '';

    public string $emailFromAddress = '';

    public string $emailFromName = '';

    public string $smtpHost = '';

    public string $smtpPort = '';

    public string $smtpUsername = '';

    public string $smtpPassword = '';

    public string $smtpEncryption = '';

    public string $emailLegalTextEu = '';

    public string $emailLegalTextEs = '';

    public bool $saved = false;

    public bool $showTestEmailModal = false;

    public string $testEmailAddress = '';

    public string $testEmailStatus = '';

    public string $testEmailError = '';

    /**
     * Maps section identifier → [Livewire property name => DB key].
     *
     * @return array<string, array<string, string>>
     */
    private function sectionFieldMap(): array
    {
        return [
            Setting::SECTION_CONTACT_FORM => [
                'adminEmail' => 'admin_email',
                'legalCheckboxTextEu' => 'legal_checkbox_text_eu',
                'legalCheckboxTextEs' => 'legal_checkbox_text_es',
            ],
            Setting::SECTION_EMAIL_CONFIGURATION => [
                'emailFromAddress' => 'from_address',
                'emailFromName' => 'from_name',
                'smtpHost' => 'smtp_host',
                'smtpPort' => 'smtp_port',
                'smtpUsername' => 'smtp_username',
                'smtpPassword' => 'smtp_password',
                'smtpEncryption' => 'smtp_encryption',
                'emailLegalTextEu' => 'legal_text_eu',
                'emailLegalTextEs' => 'legal_text_es',
            ],
            Setting::SECTION_FRONT => [
                'historyTextEu' => 'home_history_text_eu',
                'historyTextEs' => 'home_history_text_es',
                'privacyContentEu' => 'legal_page_privacy_policy_eu',
                'privacyContentEs' => 'legal_page_privacy_policy_es',
                'legalNoticeContentEu' => 'legal_page_legal_notice_eu',
                'legalNoticeContentEs' => 'legal_page_legal_notice_es',
            ],
            Setting::SECTION_RECAPTCHA => [
                'recaptchaSiteKey' => 'recaptcha_site_key',
                'recaptchaSecretKey' => 'recaptcha_secret_key',
            ],
        ];
    }

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

    /**
     * @param  array<string, mixed>  $settings
     */
    private function assignSettingValues(array $settings): void
    {
        $this->adminEmail = $settings['admin_email'] ?? '';
        $this->recaptchaSiteKey = $settings['recaptcha_site_key'] ?? '';
        $this->recaptchaSecretKey = $settings['recaptcha_secret_key'] ?? '';
        $this->legalCheckboxTextEu = $settings['legal_checkbox_text_eu'] ?? '';
        $this->legalCheckboxTextEs = $settings['legal_checkbox_text_es'] ?? '';
        $this->emailFromAddress = $settings['from_address'] ?? '';
        $this->emailFromName = $settings['from_name'] ?? '';
        $this->smtpHost = $settings['smtp_host'] ?? '';
        $this->smtpPort = $settings['smtp_port'] ?? '';
        $this->smtpUsername = $settings['smtp_username'] ?? '';
        $this->smtpPassword = $this->configuredMailSettings()->displayValue('smtp_password', $settings['smtp_password'] ?? '');
        $this->smtpEncryption = $settings['smtp_encryption'] ?? '';
        $this->emailLegalTextEu = $settings['legal_text_eu'] ?? '';
        $this->emailLegalTextEs = $settings['legal_text_es'] ?? '';
        $this->historyTextEu = $settings['home_history_text_eu'] ?? '';
        $this->historyTextEs = $settings['home_history_text_es'] ?? '';
        $this->privacyContentEu = $settings['legal_page_privacy_policy_eu'] ?? '';
        $this->privacyContentEs = $settings['legal_page_privacy_policy_es'] ?? '';
        $this->legalNoticeContentEu = $settings['legal_page_legal_notice_eu'] ?? '';
        $this->legalNoticeContentEs = $settings['legal_page_legal_notice_es'] ?? '';
    }

    /**
     * @return array<int, string>
     */
    private function resolveAvailableSections(): array
    {
        $dbSections = Setting::whereIn('section', Setting::allowedSections())
            ->distinct()
            ->orderBy('section')
            ->pluck('section')
            ->all();

        $mappedSections = array_keys($this->sectionFieldMap());
        sort($mappedSections);

        $sections = array_values(array_unique(array_merge($dbSections, $mappedSections)));
        sort($sections);

        return $sections;
    }

    /**
     * @param  array<string, string>  $map
     * @return array<int, array<string, mixed>>
     */
    private function buildUpsertRows(array $map): array
    {
        $timestamp = now();
        $rows = [];

        foreach ($map as $property => $key) {
            $rows[] = [
                'key' => $key,
                'value' => $this->configuredMailSettings()->storeValue($key, (string) $this->{$property}),
                'section' => $this->activeSection,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        return $rows;
    }

    private function configuredMailSettings(): ConfiguredMailSettings
    {
        return app(ConfiguredMailSettings::class);
    }

    public function mount(): void
    {
        $allKeys = collect($this->sectionFieldMap())->flatten()->values()->all();
        $settings = Setting::whereIn('key', $allKeys)->pluck('value', 'key');

        $this->assignSettingValues($settings->all());

        $sections = $this->resolveAvailableSections();

        $this->availableSections = $sections;
        $this->activeSection = $sections[0] ?? Setting::SECTION_GENERAL;
    }

    public function setSection(string $section): void
    {
        $normalized = Setting::normalizeSection($section);

        if (in_array($normalized, $this->availableSections, true)) {
            $this->activeSection = $normalized;
            $this->saved = false;
        }
    }

    public function save(): void
    {
        $this->validate(AdminSettingsValidation::rulesBySection($this->activeSection));

        $map = $this->sectionFieldMap()[$this->activeSection] ?? [];

        if (empty($map)) {
            $this->saved = true;

            return;
        }

        Setting::upsert($this->buildUpsertRows($map), ['key'], ['value', 'updated_at']);

        $this->saved = true;
    }

    public function openTestEmailModal(): void
    {
        $this->testEmailStatus = '';
        $this->testEmailAddress = '';
        $this->showTestEmailModal = true;
    }

    public function closeTestEmailModal(): void
    {
        $this->showTestEmailModal = false;
        $this->testEmailStatus = '';
        $this->testEmailAddress = '';
        $this->testEmailError = '';
    }

    public function sendTestEmail(): void
    {
        $this->validate([
            'testEmailAddress' => 'required|email',
        ]);

        if (empty($this->smtpHost)) {
            $this->testEmailStatus = 'error';
            $this->dispatch('notify', message: __('admin.test_email.smtp_not_configured'), type: 'error');

            return;
        }

        try {
            $emailSettings = Setting::whereIn('key', [
                'from_address',
                'from_name',
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_password',
                'smtp_encryption',
            ])->pluck('value', 'key');

            $this->configuredMailSettings()->apply($emailSettings->all());

            Mail::to($this->testEmailAddress)->send(
                new TestEmail(
                    $this->emailFromAddress,
                    $this->emailFromName,
                )
            );

            $this->testEmailStatus = 'success';
            $this->dispatch('notify', message: __('admin.test_email.sent'), type: 'success');
            $this->closeTestEmailModal();
        } catch (\Exception $e) {
            $this->testEmailStatus = 'error';
            $this->testEmailError = $e->getMessage();
            $this->dispatch('notify', message: __('admin.test_email.failed', ['error' => $e->getMessage()]), type: 'error');
        }
    }

    public function render(): View
    {
        return view('livewire.admin-settings');
    }
}

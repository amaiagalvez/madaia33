<?php

namespace App\Livewire;

use App\Mail\TestEmail;
use App\Models\Setting;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use App\Support\ConfiguredMailSettings;
use Illuminate\Support\Facades\Storage;
use App\Concerns\BuildsLocaleFieldConfigs;
use App\Validations\AdminSettingsValidation;

class AdminSettings extends Component
{
    use BuildsLocaleFieldConfigs;
    use WithFileUploads;

    public string $activeSection = '';

    /** @var array<int, string> */
    public array $availableSections = [];

    public string $adminEmail = '';

    public string $recaptchaSiteKey = '';

    public string $recaptchaSecretKey = '';

    public string $legalCheckboxTextEu = '';

    public string $legalCheckboxTextEs = '';

    public string $contactFormSubjectEu = '';

    public string $contactFormSubjectEs = '';

    public string $privacyContentEu = '';

    public string $privacyContentEs = '';

    public string $historyTextEu = '';

    public string $historyTextEs = '';

    public string $legalNoticeContentEu = '';

    public string $legalNoticeContentEs = '';

    public string $cookiePolicyContentEu = '';

    public string $cookiePolicyContentEs = '';

    public string $frontPhotoRequestTextEu = '';

    public string $frontPhotoRequestTextEs = '';

    public string $frontPrimaryEmail = '';

    public string $frontSiteName = '';

    public string $frontLogoImagePath = '';

    public mixed $frontLogoImage = null;

    public string $emailFromAddress = '';

    public string $emailFromName = '';

    public string $smtpHost = '';

    public string $smtpPort = '';

    public string $smtpUsername = '';

    public string $smtpPassword = '';

    public string $smtpEncryption = '';

    public string $emailLegalTextEu = '';

    public string $emailLegalTextEs = '';

    public string $ownersWelcomeSubjectEu = '';

    public string $ownersWelcomeSubjectEs = '';

    public string $ownersWelcomeTextEu = '';

    public string $ownersWelcomeTextEs = '';

    public bool $saved = false;

    public bool $showTestEmailModal = false;

    public string $testEmailAddress = '';

    public string $testEmailStatus = '';

    public string $testEmailError = '';

    public bool $hasUnsavedChanges = false;

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
                'contactFormSubjectEu' => 'contact_form_subject_eu',
                'contactFormSubjectEs' => 'contact_form_subject_es',
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
                'cookiePolicyContentEu' => 'legal_page_cookie_policy_eu',
                'cookiePolicyContentEs' => 'legal_page_cookie_policy_es',
                'frontPhotoRequestTextEu' => 'front_photo_request_text_eu',
                'frontPhotoRequestTextEs' => 'front_photo_request_text_es',
                'frontPrimaryEmail' => 'front_primary_email',
                'frontSiteName' => 'front_site_name',
                'frontLogoImagePath' => 'front_logo_image_path',
            ],
            Setting::SECTION_RECAPTCHA => [
                'recaptchaSiteKey' => 'recaptcha_site_key',
                'recaptchaSecretKey' => 'recaptcha_secret_key',
            ],
            Setting::SECTION_OWNERS => [
                'ownersWelcomeSubjectEu' => 'owners_welcome_subject_eu',
                'ownersWelcomeSubjectEs' => 'owners_welcome_subject_es',
                'ownersWelcomeTextEu' => 'owners_welcome_text_eu',
                'ownersWelcomeTextEs' => 'owners_welcome_text_es',
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
        $this->contactFormSubjectEu = $settings['contact_form_subject_eu'] ?? '';
        $this->contactFormSubjectEs = $settings['contact_form_subject_es'] ?? '';
        $this->emailFromAddress = $settings['from_address'] ?? '';
        $this->emailFromName = $settings['from_name'] ?? '';
        $this->smtpHost = $settings['smtp_host'] ?? '';
        $this->smtpPort = $settings['smtp_port'] ?? '';
        $this->smtpUsername = $settings['smtp_username'] ?? '';
        $this->smtpPassword = $this->configuredMailSettings()->displayValue('smtp_password', $settings['smtp_password'] ?? '');
        $this->smtpEncryption = $settings['smtp_encryption'] ?? '';
        $this->emailLegalTextEu = $settings['legal_text_eu'] ?? '';
        $this->emailLegalTextEs = $settings['legal_text_es'] ?? '';
        $this->ownersWelcomeSubjectEu = $settings['owners_welcome_subject_eu'] ?? '';
        $this->ownersWelcomeSubjectEs = $settings['owners_welcome_subject_es'] ?? '';
        $this->ownersWelcomeTextEu = $settings['owners_welcome_text_eu'] ?? '';
        $this->ownersWelcomeTextEs = $settings['owners_welcome_text_es'] ?? '';
        $this->historyTextEu = $settings['home_history_text_eu'] ?? '';
        $this->historyTextEs = $settings['home_history_text_es'] ?? '';
        $this->privacyContentEu = $settings['legal_page_privacy_policy_eu'] ?? '';
        $this->privacyContentEs = $settings['legal_page_privacy_policy_es'] ?? '';
        $this->legalNoticeContentEu = $settings['legal_page_legal_notice_eu'] ?? '';
        $this->legalNoticeContentEs = $settings['legal_page_legal_notice_es'] ?? '';
        $this->cookiePolicyContentEu = $settings['legal_page_cookie_policy_eu'] ?? '';
        $this->cookiePolicyContentEs = $settings['legal_page_cookie_policy_es'] ?? '';
        $this->frontPhotoRequestTextEu = $settings['front_photo_request_text_eu'] ?? '';
        $this->frontPhotoRequestTextEs = $settings['front_photo_request_text_es'] ?? '';
        $this->frontPrimaryEmail = $settings['front_primary_email'] ?? '';
        $this->frontSiteName = $settings['front_site_name'] ?? '';
        $this->frontLogoImagePath = $settings['front_logo_image_path'] ?? '';
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
        $settings = Setting::stringValues($allKeys);

        $this->assignSettingValues($settings);

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
            $this->hasUnsavedChanges = false;
        }
    }

    public function updated(string $property): void
    {
        if ($property === 'activeSection' || str_starts_with($property, 'testEmail') || $property === 'showTestEmailModal') {
            return;
        }

        $this->saved = false;
        $this->hasUnsavedChanges = true;
    }

    public function save(): void
    {
        $this->validate(AdminSettingsValidation::rulesBySection($this->activeSection));

        if ($this->activeSection === Setting::SECTION_FRONT && $this->frontLogoImage !== null) {
            $this->frontLogoImagePath = $this->frontLogoImage->store('branding', 'public');
        }

        $map = $this->sectionFieldMap()[$this->activeSection] ?? [];

        if (empty($map)) {
            $this->saved = true;

            return;
        }

        Setting::upsert($this->buildUpsertRows($map), ['key'], ['value', 'updated_at']);
        Setting::refreshStringValuesCache();

        if ($this->frontLogoImage !== null) {
            $this->frontLogoImage = null;
        }

        $this->saved = true;
        $this->hasUnsavedChanges = false;
    }

    public function getFrontLogoPreviewUrlProperty(): ?string
    {
        if ($this->frontLogoImage !== null) {
            return $this->frontLogoImage->temporaryUrl();
        }

        $logoPath = trim($this->frontLogoImagePath);

        if ($logoPath === '') {
            return null;
        }

        if (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')) {
            return $logoPath;
        }

        if (str_starts_with($logoPath, '/')) {
            return $logoPath;
        }

        if (Storage::disk('public')->exists($logoPath)) {
            return Storage::url($logoPath);
        }

        return asset('storage/'.ltrim($logoPath, '/'));
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
            $emailSettings = Setting::stringValues([
                'from_address',
                'from_name',
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_password',
                'smtp_encryption',
            ]);

            $this->configuredMailSettings()->apply($emailSettings);

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
        return view('livewire.admin.settings');
    }
}

<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;
use App\SupportedLocales;
use Illuminate\Contracts\View\View;
use App\Validations\AdminSettingsValidation;

class AdminSettings extends Component
{
    public string $activeSection = '';

    /** @var array<int, string> */
    public array $availableSections = [];

    public string $adminEmail = '';

    public string $recaptchaSiteKey = '';

    public string $recaptchaSecretKey = '';

    public string $legalCheckboxTextEu = '';

    public string $legalCheckboxTextEs = '';

    public string $legalUrl = '';

    public string $privacyContentEu = '';

    public string $privacyContentEs = '';

    public string $legalNoticeContentEu = '';

    public string $legalNoticeContentEs = '';

    public bool $saved = false;

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
                'legalUrl' => 'legal_url',
            ],
            Setting::SECTION_FRONT => [
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
        $this->legalUrl = $settings['legal_url'] ?? '';
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
                'value' => $this->{$property},
                'section' => $this->activeSection,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        return $rows;
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

    /**
     * @return array<string, array{field: string, fieldLabel: string, value: string}>
     */
    public function localeConfigsFor(string $propertyBase, string $labelKeyBase): array
    {
        return collect(SupportedLocales::all())
            ->mapWithKeys(function (string $locale) use ($propertyBase, $labelKeyBase): array {
                $suffix = SupportedLocales::propertySuffix($locale);
                $field = "{$propertyBase}{$suffix}";

                return [
                    $locale => [
                        'field' => $field,
                        'fieldLabel' => __("{$labelKeyBase}_{$locale}"),
                        'value' => (string) ($this->{$field} ?? ''),
                    ],
                ];
            })
            ->all();
    }

    public function render(): View
    {
        return view('livewire.admin-settings');
    }
}

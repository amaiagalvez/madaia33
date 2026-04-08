<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\SupportedLocales;
use Illuminate\Contracts\View\View;

class LegalPageController extends Controller
{
    public function privacyPolicy(): View
    {
        return $this->renderLegalPage('privacy_policy', 'general.footer.privacy_policy', 'privacy-policy');
    }

    public function legalNotice(): View
    {
        return $this->renderLegalPage('legal_notice', 'general.footer.legal_notice', 'legal-notice');
    }

    private function renderLegalPage(string $pageKey, string $titleKey, string $pageSlug): View
    {
        $localizedKeys = SupportedLocales::localizedKeys("legal_page_{$pageKey}");
        $settings = Setting::query()
            ->whereIn('key', $localizedKeys)
            ->pluck('value', 'key');

        $content = '';

        foreach ($localizedKeys as $localizedKey) {
            if ($settings->has($localizedKey)) {
                $content = (string) $settings[$localizedKey];

                break;
            }
        }

        return view('public.legal-page', [
            'content' => $content,
            'pageSlug' => $pageSlug,
            'titleKey' => $titleKey,
        ]);
    }
}

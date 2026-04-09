<?php

namespace App\Http\Controllers;

use App\Models\Setting;
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
        $content = Setting::localizedString("legal_page_{$pageKey}", '') ?? '';

        return view('public.legal-page', [
            'content' => $content,
            'pageSlug' => $pageSlug,
            'titleKey' => $titleKey,
        ]);
    }
}

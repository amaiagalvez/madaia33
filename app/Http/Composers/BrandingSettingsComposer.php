<?php

namespace App\Http\Composers;

use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class BrandingSettingsComposer
{
    /**
     * @var array<string, string>|null
     */
    private static ?array $cachedSettings = null;

    public function compose(View $view): void
    {
        $settings = self::settings();
        $siteName = trim((string) ($settings['front_site_name'] ?? ''));
        $frontEmail = trim((string) ($settings['front_primary_email'] ?? ''));
        $logoPath = trim((string) ($settings['front_logo_image_path'] ?? ''));

        $view->with([
            'publicSiteName' => $siteName !== '' ? $siteName : config('app.name', 'Madaia 33'),
            'publicPrimaryEmail' => $frontEmail !== '' ? $frontEmail : 'info@madaia33.eus',
            'publicLogoUrl' => $this->resolveLogoUrl($logoPath),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private static function settings(): array
    {
        if (self::$cachedSettings !== null) {
            return self::$cachedSettings;
        }

        self::$cachedSettings = Setting::stringValues([
            'front_site_name',
            'front_primary_email',
            'front_logo_image_path',
        ]);

        return self::$cachedSettings;
    }

    private function resolveLogoUrl(string $logoPath): string
    {
        if ($logoPath === '') {
            return asset('storage/madaia33/madaia33.png');
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

        return asset('storage/' . ltrim($logoPath, '/'));
    }
}

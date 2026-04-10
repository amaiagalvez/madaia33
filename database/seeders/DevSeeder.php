<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\Location;
use App\Models\Notice;
use App\Models\Setting;
use Illuminate\Support\Str;
use App\Models\ContactMessage;
use App\Models\NoticeLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedMailhogSettings();
        $this->seedNotices();
        $this->seedImages();
        $this->seedContactMessages();
    }

    // -------------------------------------------------------------------------

    private function seedMailhogSettings(): void
    {
        // Configure for mailhog in local development
        Setting::upsert([
            ['key' => 'from_address', 'value' => 'madaia33@mailhog.local', 'section' => Setting::SECTION_EMAIL_CONFIGURATION],
            ['key' => 'from_name', 'value' => 'Madaia 33 Local', 'section' => Setting::SECTION_EMAIL_CONFIGURATION],
            ['key' => 'smtp_host', 'value' => 'mailhog', 'section' => Setting::SECTION_EMAIL_CONFIGURATION],
            ['key' => 'smtp_port', 'value' => '1025', 'section' => Setting::SECTION_EMAIL_CONFIGURATION],
            ['key' => 'smtp_username', 'value' => '', 'section' => Setting::SECTION_EMAIL_CONFIGURATION],
            ['key' => 'smtp_password', 'value' => '', 'section' => Setting::SECTION_EMAIL_CONFIGURATION],
            ['key' => 'smtp_encryption', 'value' => '', 'section' => Setting::SECTION_EMAIL_CONFIGURATION],
        ], ['key'], ['value', 'section']);
    }

    // -------------------------------------------------------------------------

    private function seedNotices(): void
    {
        // Aviso general (sin ubicación) — público
        Notice::factory()->public()->create([
            'title_eu' => 'Ongi etorri komunitate-webera',
            'title_es' => 'Bienvenidos a la web de la comunidad',
            'content_eu' => 'Hemen aurkituko dituzu komunitateari buruzko azken berriak eta iragarkiak.',
            'content_es' => 'Aquí encontrarás las últimas noticias y avisos de la comunidad.',
            'published_at' => now()->subDays(1),
        ]);

        // Aviso solo en euskera (sin traducción al castellano) — público
        Notice::factory()->public()->euOnly()->create([
            'title_eu' => 'Iragarkia euskaraz soilik',
            'content_eu' => 'Iragarki hau euskaraz bakarrik dago. Gaztelaniazko itzulpenik ez dago.',
            'published_at' => now()->subDays(2),
        ]);

        // Aviso para portal 33-A — público
        $portalA = Notice::factory()->public()->create([
            'title_eu' => '33-A Atariko iragarkia',
            'title_es' => 'Aviso para el portal 33-A',
            'content_eu' => '33-A atariko bizilagunei zuzendutako iragarkia.',
            'content_es' => 'Aviso dirigido a los vecinos del portal 33-A.',
            'published_at' => now()->subDays(3),
        ]);
        $this->attachNoticeToLocationCode($portalA, '33-A');

        // Aviso para portal 33-B — público
        $portalB = Notice::factory()->public()->create([
            'title_eu' => '33-B Atariko iragarkia',
            'title_es' => 'Aviso para el portal 33-B',
            'content_eu' => '33-B atariko bizilagunei zuzendutako iragarkia.',
            'content_es' => 'Aviso dirigido a los vecinos del portal 33-B.',
            'published_at' => now()->subDays(4),
        ]);
        $this->attachNoticeToLocationCode($portalB, '33-B');

        // Aviso para garaje P-1 — público
        $garage = Notice::factory()->public()->create([
            'title_eu' => 'P-1 aparkalekuko iragarkia',
            'title_es' => 'Aviso para el garaje P-1',
            'content_eu' => 'P-1 aparkalekuko bizilagunei zuzendutako iragarkia.',
            'content_es' => 'Aviso dirigido a los vecinos del garaje P-1.',
            'published_at' => now()->subDays(5),
        ]);
        $this->attachNoticeToLocationCode($garage, 'P-1');

        // Aviso privado (no visible en parte pública)
        Notice::factory()->private()->create([
            'title_eu' => 'Iragarki pribatua (ez da publikoa)',
            'title_es' => 'Aviso privado (no público)',
            'content_eu' => 'Iragarki hau ez da publikoa.',
            'content_es' => 'Este aviso no es público.',
            'published_at' => now()->subDays(6),
        ]);

        // 8 avisos aleatorios públicos para probar la paginación (>10 total)
        Notice::factory()->public()->count(8)->create()->each(function (Notice $notice) {
            // Asignar aleatoriamente a un portal o sin ubicación
            if (fake()->boolean(60)) {
                $portalCode = Location::query()
                    ->where('type', 'portal')
                    ->inRandomOrder()
                    ->value('code');

                if ($portalCode !== null) {
                    $this->attachNoticeToLocationCode($notice, $portalCode);
                }
            }
        });
    }

    private function attachNoticeToLocationCode(Notice $notice, string $code): void
    {
        $locationId = Location::query()->where('code', $code)->value('id');

        if ($locationId === null) {
            return;
        }

        NoticeLocation::create([
            'notice_id' => $notice->id,
            'location_id' => $locationId,
        ]);
    }

    // -------------------------------------------------------------------------

    private function seedImages(): void
    {
        $items = [
            [
                'alt_eu' => 'Komunitate-etxea',
                'alt_es' => 'Casa de la comunidad',
                'colors' => ['#0f172a', '#155e75'],
                'tag' => Image::TAG_MADAIA,
            ],
            [
                'alt_eu' => '33-A atariko sarrera',
                'alt_es' => 'Entrada portal 33-A',
                'colors' => ['#1e293b', '#1d4ed8'],
                'tag' => Image::TAG_HISTORY,
            ],
            [
                'alt_eu' => '33-B atariko sarrera',
                'alt_es' => 'Entrada portal 33-B',
                'colors' => ['#312e81', '#7c3aed'],
                'tag' => Image::TAG_HISTORY,
            ],
            [
                'alt_eu' => 'P-1 aparkalekua',
                'alt_es' => 'Garaje P-1',
                'colors' => ['#78350f', '#ea580c'],
                'tag' => Image::TAG_HISTORY,
            ],
            [
                'alt_eu' => 'Lorategia',
                'alt_es' => 'Jardín comunitario',
                'colors' => ['#14532d', '#16a34a'],
                'tag' => Image::TAG_MADAIA,
            ],
        ];

        Storage::disk('public')->makeDirectory('images');

        foreach ($items as $index => $item) {
            $filename = Str::uuid() . '.svg';
            $path = 'images/' . $filename;
            $imageText = $item['alt_es'];

            Storage::disk('public')->put(
                $path,
                $this->buildSeededImageSvg($imageText, $item['colors'][0], $item['colors'][1], $index + 1)
            );

            Image::create([
                'filename' => $filename,
                'path' => $path,
                'alt_text_eu' => $item['alt_eu'],
                'alt_text_es' => $item['alt_es'],
                'tag' => $item['tag'] ?? null,
            ]);
        }
    }

    private function buildSeededImageSvg(string $label, string $startColor, string $endColor, int $number): string
    {
        $escapedLabel = e($label);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800" role="img" aria-label="{$escapedLabel}">
    <defs>
        <linearGradient id="bg-{$number}" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="{$startColor}" />
            <stop offset="100%" stop-color="{$endColor}" />
        </linearGradient>
    </defs>
    <rect width="1200" height="800" fill="url(#bg-{$number})" />
    <circle cx="960" cy="200" r="170" fill="rgba(255,255,255,0.12)" />
    <rect x="90" y="560" width="1020" height="160" rx="24" fill="rgba(15,23,42,0.45)" />
    <text x="140" y="650" fill="#ffffff" font-size="52" font-family="Arial, sans-serif" font-weight="700">{$escapedLabel}</text>
    <text x="140" y="695" fill="rgba(255,255,255,0.8)" font-size="26" font-family="Arial, sans-serif">Comunidad 33 · Imagen de prueba {$number}</text>
</svg>
SVG;
    }

    // -------------------------------------------------------------------------

    private function seedContactMessages(): void
    {
        // Mensajes no leídos
        ContactMessage::factory()->unread()->count(3)->create();

        // Mensajes leídos
        ContactMessage::factory()->read()->count(2)->create();

        // Un mensaje con asunto reconocible para pruebas manuales
        ContactMessage::factory()->unread()->create([
            'name' => 'Ane Etxebarria',
            'email' => 'ane@example.com',
            'subject' => 'Galdera komunitateari buruz',
            'message' => 'Kaixo, komunitate-bileraren data jakin nahi nuke. Eskerrik asko.',
        ]);
    }
}

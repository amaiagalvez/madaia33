<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\Notice;
use App\CommunityLocations;
use Illuminate\Support\Str;
use App\Models\ContactMessage;
use App\Models\NoticeLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedNotices();
        $this->seedImages();
        $this->seedContactMessages();
    }

    // -------------------------------------------------------------------------

    private function seedNotices(): void
    {
        // Aviso general (sin ubicación) — público
        $general = Notice::factory()->public()->create([
            'title_eu' => 'Ongi etorri komunitate-webera',
            'title_es' => 'Bienvenidos a la web de la comunidad',
            'content_eu' => 'Hemen aurkituko dituzu komunitateari buruzko azken berriak eta iragarkiak.',
            'content_es' => 'Aquí encontrarás las últimas noticias y avisos de la comunidad.',
            'published_at' => now()->subDays(1),
        ]);

        // Aviso solo en euskera (sin traducción al castellano) — público
        $euOnly = Notice::factory()->public()->euOnly()->create([
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
        NoticeLocation::create(['notice_id' => $portalA->id, 'location_type' => 'portal', 'location_code' => '33-A']);

        // Aviso para portal 33-B — público
        $portalB = Notice::factory()->public()->create([
            'title_eu' => '33-B Atariko iragarkia',
            'title_es' => 'Aviso para el portal 33-B',
            'content_eu' => '33-B atariko bizilagunei zuzendutako iragarkia.',
            'content_es' => 'Aviso dirigido a los vecinos del portal 33-B.',
            'published_at' => now()->subDays(4),
        ]);
        NoticeLocation::create(['notice_id' => $portalB->id, 'location_type' => 'portal', 'location_code' => '33-B']);

        // Aviso para garaje P-1 — público
        $garage = Notice::factory()->public()->create([
            'title_eu' => 'P-1 aparkalekuko iragarkia',
            'title_es' => 'Aviso para el garaje P-1',
            'content_eu' => 'P-1 aparkalekuko bizilagunei zuzendutako iragarkia.',
            'content_es' => 'Aviso dirigido a los vecinos del garaje P-1.',
            'published_at' => now()->subDays(5),
        ]);
        NoticeLocation::create(['notice_id' => $garage->id, 'location_type' => 'garage', 'location_code' => 'P-1']);

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
                $portal = fake()->randomElement(CommunityLocations::PORTALS);
                NoticeLocation::create([
                    'notice_id' => $notice->id,
                    'location_type' => 'portal',
                    'location_code' => $portal,
                ]);
            }
        });
    }

    // -------------------------------------------------------------------------

    private function seedImages(): void
    {
        // Crear un placeholder SVG reutilizable en storage
        $placeholder = $this->createPlaceholderImage();

        $items = [
            ['alt_eu' => 'Komunitate-etxea', 'alt_es' => 'Casa de la comunidad'],
            ['alt_eu' => '33-A atariko sarrera', 'alt_es' => 'Entrada portal 33-A'],
            ['alt_eu' => '33-B atariko sarrera', 'alt_es' => 'Entrada portal 33-B'],
            ['alt_eu' => 'P-1 aparkalekua', 'alt_es' => 'Garaje P-1'],
            ['alt_eu' => 'Lorategia', 'alt_es' => 'Jardín comunitario'],
        ];

        foreach ($items as $item) {
            $filename = Str::uuid().'.jpg';
            Storage::disk('public')->copy($placeholder, 'images/'.$filename);

            $image = Image::create([
                'filename' => $filename,
                'path' => 'images/'.$filename,
                'alt_text_eu' => $item['alt_eu'],
                'alt_text_es' => $item['alt_es'],
            ]);
        }
    }

    private function createPlaceholderImage(): string
    {
        $path = 'images/dev-placeholder.jpg';

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->makeDirectory('images');

            // SVG mínimo convertido a contenido binario simulado (JPEG válido 1×1 px)
            $jpeg1x1 = base64_decode(
                '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8U'.
                    'HRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgN'.
                    'DRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
                    'MjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAA'.
                    'AAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/EABQRAQAAAAAAAAAAAAAAAAAA'.
                    'AAD/2gAMAwEAAhEDEQA/AJAA/9k='
            );

            Storage::disk('public')->put($path, $jpeg1x1);
        }

        return $path;
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

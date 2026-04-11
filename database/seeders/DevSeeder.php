<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Image;
use App\Models\Owner;
use App\Models\Notice;
use App\Models\Voting;
use App\Models\Setting;
use App\Models\Location;
use App\Models\Property;
use Illuminate\Support\Str;
use App\Models\ContactMessage;
use App\Models\NoticeLocation;
use Illuminate\Database\Seeder;
use App\Models\UserLoginSession;
use App\Models\PropertyAssignment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Support\VotingEligibilityService;
use App\Actions\Votings\CastVotingBallotAction;

class DevSeeder extends Seeder
{
    private const GENERAL_ADMIN_EMAIL = 'admin.general@madaia33.eus';

    private const COMMUNITY_ADMIN_EMAIL = 'admin.comunidad@madaia33.eus';

    private const PROPERTY_OWNER_EMAIL = 'propietaria@madaia33.eus';

    private const DELEGATED_VOTE_EMAIL = 'voto.delegado@madaia33.eus';

    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            LocationSeeder::class,
        ]);

        $this->seedMailhogSettings();
        $this->seedNotices();
        $this->seedImages();
        $this->seedOwnersAndProperties();
        $this->seedRoleUsers();
        $this->seedUserLoginSessions();
        $this->call(VotingSeeder::class);
        $this->seedVotingBallots();
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
            // Asignar aleatoriamente a un portal/local o sin ubicación
            if (fake()->boolean(60)) {
                $locationCode = Location::query()
                    ->whereIn('type', ['portal', 'local'])
                    ->inRandomOrder()
                    ->value('code');

                if ($locationCode !== null) {
                    $this->attachNoticeToLocationCode($notice, $locationCode);
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

    private function seedOwnersAndProperties(): void
    {
        $locationIds = Location::query()
            ->whereIn('type', ['portal', 'local', 'garage'])
            ->pluck('id');

        if ($locationIds->isEmpty()) {
            $locationIds = Location::factory()->portal()->count(2)->create(['name' => 'Portal demo'])->pluck('id')
                ->merge(Location::factory()->local()->count(1)->create(['name' => 'Local demo'])->pluck('id'))
                ->merge(Location::factory()->garage()->count(1)->create(['name' => 'Garaje demo'])->pluck('id'));
        }

        foreach (range(1, 12) as $_) {
            Property::factory()->create([
                'location_id' => $locationIds->random(),
            ]);
        }

        $ownerUsers = User::factory()
            ->count(10)
            ->create();

        $owners = $ownerUsers->map(function (User $user): Owner {
            $user->assignRole(Role::PROPERTY_OWNER);

            return Owner::factory()->create([
                'user_id' => $user->id,
                'coprop1_name' => $user->name,
                'coprop1_email' => $user->email,
            ]);
        });

        $propertyIds = Property::query()
            ->doesntHave('activeAssignments')
            ->inRandomOrder()
            ->limit($owners->count())
            ->pluck('id')
            ->values();

        $owners
            ->take($propertyIds->count())
            ->values()
            ->each(function (Owner $owner, int $index) use ($propertyIds): void {
                PropertyAssignment::factory()->validated()->create([
                    'property_id' => $propertyIds[$index],
                    'owner_id' => $owner->id,
                    'start_date' => now()->subMonths(fake()->numberBetween(1, 24))->format('Y-m-d'),
                    'end_date' => null,
                ]);
            });
    }

    private function seedRoleUsers(): void
    {
        $this->seedGeneralAdminUser();
        $this->seedCommunityAdminUser();
        $this->seedPropertyOwnerUser();
        $this->seedDelegatedVoteUser();
    }

    private function seedUserLoginSessions(): void
    {
        $users = User::query()
            ->orderBy('id')
            ->limit(3)
            ->get(['id']);

        foreach ($users as $user) {
            UserLoginSession::factory()->closed()->create([
                'user_id' => $user->id,
            ]);

            UserLoginSession::factory()->create([
                'user_id' => $user->id,
            ]);
        }
    }

    private function seedGeneralAdminUser(): void
    {
        $this->upsertRoleUser(
            email: self::GENERAL_ADMIN_EMAIL,
            name: 'Admin General Demo',
            roles: [Role::GENERAL_ADMIN],
        );
    }

    private function seedCommunityAdminUser(): void
    {
        $user = $this->upsertRoleUser(
            email: self::COMMUNITY_ADMIN_EMAIL,
            name: 'Admin Comunidad Demo',
            roles: [Role::COMMUNITY_ADMIN],
        );

        $managedLocationId = Location::query()
            ->whereIn('type', ['portal', 'local'])
            ->orderBy('id')
            ->value('id');

        $user->managedLocations()->sync($managedLocationId === null ? [] : [$managedLocationId]);
    }

    private function seedPropertyOwnerUser(): void
    {
        $user = $this->upsertRoleUser(
            email: self::PROPERTY_OWNER_EMAIL,
            name: 'Propietaria Demo',
            roles: [Role::PROPERTY_OWNER],
        );

        $owner = Owner::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'coprop1_name' => $user->name,
                'coprop1_dni' => '00000000A',
                'coprop1_email' => $user->email,
                'coprop1_phone' => '600000001',
            ],
        );

        $existingPropertyId = $owner->activeAssignments()
            ->orderBy('id')
            ->value('property_id');

        $preferredPropertyId = Property::query()
            ->doesntHave('activeAssignments')
            ->whereHas('location', function ($query): void {
                $query->whereIn('code', ['33-A', '33-B', '33-C']);
            })
            ->orderBy('id')
            ->value('id');

        if ($preferredPropertyId === null) {
            $preferredLocationId = Location::query()
                ->whereIn('code', ['33-A', '33-B', '33-C'])
                ->orderBy('id')
                ->value('id');

            if ($preferredLocationId !== null) {
                $preferredPropertyId = Property::query()->firstOrCreate(
                    [
                        'location_id' => $preferredLocationId,
                        'name' => 'DEMO-OWNER',
                    ],
                    [
                        'community_pct' => null,
                        'location_pct' => null,
                    ],
                )->id;
            }
        }

        $propertyId = $existingPropertyId
            ?? $preferredPropertyId
            ?? Property::query()->doesntHave('activeAssignments')->orderBy('id')->value('id');

        if ($propertyId === null) {
            return;
        }

        PropertyAssignment::query()->updateOrCreate(
            [
                'owner_id' => $owner->id,
                'property_id' => $propertyId,
                'end_date' => null,
            ],
            [
                'start_date' => now()->subMonths(6)->format('Y-m-d'),
            ],
        );

        $secondaryPropertyId = Property::query()
            ->doesntHave('activeAssignments')
            ->where('id', '!=', $propertyId)
            ->orderBy('id')
            ->value('id');

        if ($secondaryPropertyId === null) {
            return;
        }

        PropertyAssignment::query()->updateOrCreate(
            [
                'owner_id' => $owner->id,
                'property_id' => $secondaryPropertyId,
                'end_date' => null,
            ],
            [
                'start_date' => now()->subMonths(3)->format('Y-m-d'),
            ],
        );
    }

    private function seedDelegatedVoteUser(): void
    {
        $this->upsertRoleUser(
            email: self::DELEGATED_VOTE_EMAIL,
            name: 'Voto Delegado Demo',
            roles: [Role::DELEGATED_VOTE],
        );
    }

    private function seedVotingBallots(): void
    {
        /** @var CastVotingBallotAction $castVotingBallotAction */
        $castVotingBallotAction = app(CastVotingBallotAction::class);

        /** @var VotingEligibilityService $eligibilityService */
        $eligibilityService = app(VotingEligibilityService::class);

        $delegatedUser = User::query()->where('email', self::DELEGATED_VOTE_EMAIL)->first();

        $propertyOwner = Owner::query()
            ->with('user')
            ->whereHas('user', function ($query): void {
                $query->where('email', self::PROPERTY_OWNER_EMAIL);
            })
            ->first();

        Voting::query()
            ->with(['options', 'locations.location'])
            ->publishedOpen()
            ->orderBy('id')
            ->get()
            ->each(function (Voting $voting) use ($castVotingBallotAction, $eligibilityService, $delegatedUser, $propertyOwner): void {
                $eligibleOwners = $eligibilityService->eligibleOwners($voting)->values();

                if ($eligibleOwners->isEmpty() || $voting->options->isEmpty()) {
                    return;
                }

                $selfVotingOwner = $propertyOwner !== null && $eligibleOwners->contains('id', $propertyOwner->id)
                    ? $eligibleOwners->firstWhere('id', $propertyOwner->id)
                    : $eligibleOwners->first();

                if ($selfVotingOwner instanceof Owner && $selfVotingOwner->user !== null) {
                    $castVotingBallotAction->execute(
                        $voting,
                        $selfVotingOwner,
                        $voting->options->first()->id,
                        $selfVotingOwner->user,
                    );
                }

                $delegatedOwner = $eligibleOwners->first(
                    fn (Owner $owner): bool => $selfVotingOwner === null || $owner->id !== $selfVotingOwner->id,
                );

                if (! $delegatedOwner instanceof Owner || ! $delegatedUser instanceof User) {
                    return;
                }

                $delegatedOption = $voting->options->skip(1)->first() ?? $voting->options->first();

                $castVotingBallotAction->execute(
                    $voting,
                    $delegatedOwner,
                    $delegatedOption->id,
                    $delegatedUser,
                );
            });
    }

    /**
     * @param  array<int, string>  $roles
     */
    private function upsertRoleUser(string $email, string $name, array $roles): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        );

        $user->syncRoleNames($roles);

        return $user;
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

<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Image;
use App\Models\Owner;
use App\Models\Voting;
use App\Models\Property;
use App\Models\VotingBallot;
use App\Models\VotingSelection;
use Database\Seeders\DevSeeder;
use App\Models\VotingOptionTotal;
use App\Models\PropertyAssignment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

test('dev seeder creates visible gallery images in public storage', function () {
    Storage::fake('public');
    Mail::fake();

    (new DevSeeder)->run();

    $images = Image::query()->orderBy('id')->get();

    expect($images)->toHaveCount(5);

    foreach ($images as $image) {
        expect(pathinfo($image->filename, PATHINFO_EXTENSION))->toBe('svg');
        expect(Storage::disk('public')->exists($image->path))->toBeTrue();
    }

    $firstImageSvg = Storage::disk('public')->get($images->first()->path);

    expect($firstImageSvg)
        ->toContain('<svg')
        ->toContain('Imagen de prueba 1');
});

test('dev seeder creates owners, role users and seeded voting ballots', function () {
    Mail::fake();

    (new DevSeeder)->run();

    expect(Owner::query()->count())->toBeGreaterThanOrEqual(10);
    expect(User::query()->whereHas('owner')->count())->toBeGreaterThanOrEqual(10);
    expect(Property::query()->count())->toBeGreaterThanOrEqual(12);
    expect(PropertyAssignment::query()->active()->count())->toBeGreaterThan(0);
    expect(Voting::query()->count())->toBeGreaterThanOrEqual(4);
    expect(Voting::query()->where('is_anonymous', true)->count())->toBeGreaterThan(0);
    expect(Voting::query()->where('is_anonymous', false)->count())->toBeGreaterThan(0);
    expect(Voting::query()->withCount('options')->get()->min('options_count'))->toBeGreaterThanOrEqual(2);

    expect(
        User::query()
            ->where('email', 'admin.general@madaia33.eus')
            ->whereHas('roles', fn ($query) => $query->where('name', Role::GENERAL_ADMIN))
            ->exists()
    )->toBeTrue();

    $communityAdmin = User::query()
        ->where('email', 'admin.comunidad@madaia33.eus')
        ->whereHas('roles', fn ($query) => $query->where('name', Role::COMMUNITY_ADMIN))
        ->first();

    expect($communityAdmin)->not->toBeNull();
    expect($communityAdmin->managedLocations()->count())->toBeGreaterThan(0);

    $propertyOwner = User::query()
        ->where('email', 'propietaria@madaia33.eus')
        ->whereHas('roles', fn ($query) => $query->where('name', Role::PROPERTY_OWNER))
        ->first();

    expect($propertyOwner)->not->toBeNull();
    expect($propertyOwner->owner)->not->toBeNull();
    expect($propertyOwner->owner->activeAssignments()->count())->toBeGreaterThan(0);

    expect(
        User::query()
            ->where('email', 'voto.delegado@madaia33.eus')
            ->whereHas('roles', fn ($query) => $query->where('name', Role::DELEGATED_VOTE))
            ->exists()
    )->toBeTrue();

    $combinedAdmin = User::query()
        ->where('email', 'admin.konbinatua@madaia33.eus')
        ->first();

    expect($combinedAdmin)->not->toBeNull();
    expect($combinedAdmin->hasRole(Role::GENERAL_ADMIN))->toBeTrue();
    expect($combinedAdmin->hasRole(Role::COMMUNITY_ADMIN))->toBeTrue();
    expect($combinedAdmin->hasRole(Role::DELEGATED_VOTE))->toBeTrue();
    expect($combinedAdmin->managedLocations()->count())->toBeGreaterThan(0);

    $ownerDelegated = User::query()
        ->where('email', 'propietaria.delegada@madaia33.eus')
        ->first();

    expect($ownerDelegated)->not->toBeNull();
    expect($ownerDelegated->hasRole(Role::PROPERTY_OWNER))->toBeTrue();
    expect($ownerDelegated->hasRole(Role::DELEGATED_VOTE))->toBeTrue();
    expect($ownerDelegated->owner)->not->toBeNull();
    expect($ownerDelegated->owner->activeAssignments()->count())->toBeGreaterThan(0);

    expect(VotingBallot::query()->count())->toBeGreaterThan(0);
    expect(VotingBallot::query()->whereNotNull('cast_by_user_id')->count())->toBeGreaterThan(0);
    expect(VotingSelection::query()->count())->toBeGreaterThan(0);
    expect(VotingOptionTotal::query()->sum('votes_count'))->toBe(VotingBallot::query()->count());
});

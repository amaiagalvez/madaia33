<?php

use App\Models\Image;
use App\Models\Owner;
use App\Models\Property;
use App\Models\PropertyAssignment;
use App\Models\User;
use App\Models\Voting;
use Database\Seeders\DevSeeder;
use Illuminate\Support\Facades\Storage;

test('dev seeder creates visible gallery images in public storage', function () {
    Storage::fake('public');

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

test('dev seeder creates owners and random properties with active assignments', function () {
    (new DevSeeder)->run();

    expect(Owner::query()->count())->toBeGreaterThanOrEqual(10);
    expect(User::query()->whereHas('owner')->count())->toBeGreaterThanOrEqual(10);
    expect(Property::query()->count())->toBeGreaterThanOrEqual(12);
    expect(PropertyAssignment::query()->active()->count())->toBeGreaterThan(0);
    expect(Voting::query()->count())->toBeGreaterThanOrEqual(4);
    expect(Voting::query()->where('is_anonymous', true)->count())->toBeGreaterThan(0);
    expect(Voting::query()->where('is_anonymous', false)->count())->toBeGreaterThan(0);
    expect(Voting::query()->withCount('options')->get()->min('options_count'))->toBeGreaterThanOrEqual(2);
});

<?php

use App\Models\Image;
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

<?php

use App\Models\NoticeTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\UniqueConstraintViolationException;

uses(RefreshDatabase::class);

it('rejects duplicate notice tag slugs', function () {
  $baseName = fake()->words(3, true);
  $slug = str(fake()->slug())->value();

  NoticeTag::factory()->create([
    'slug' => $slug,
    'name_eu' => $baseName,
  ]);

  NoticeTag::factory()->create([
    'slug' => $slug,
  ]);
})->throws(UniqueConstraintViolationException::class)->repeat(2);

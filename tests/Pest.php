<?php

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Notice;
use App\Models\Setting;
use Tests\DuskTestCase;
use App\Models\Location;
use App\Models\NoticeLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->extend(DuskTestCase::class)
    //  ->use(Illuminate\Foundation\Testing\DatabaseMigrations::class)
    ->in('Browser');

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function settingValue(string $key, mixed $default = null): mixed
{
    return Setting::where('key', $key)->value('value') ?? $default;
}

/*
|--------------------------------------------------------------------------
| Global Setup
|--------------------------------------------------------------------------
*/

function createSetting(string $key, mixed $value): Setting
{
    return Setting::factory()->forKey($key, (string) $value)->create();
}

function attachNoticeToLocationCode(Notice $notice, string $code): NoticeLocation
{
    $locationId = Location::query()->where('code', $code)->value('id');

    if ($locationId === null) {
        $locationId = Location::factory()->create([
            'type' => str_starts_with($code, 'P-') ? 'garage' : 'portal',
            'code' => $code,
            'name' => 'Location '.$code,
        ])->id;
    }

    return NoticeLocation::create([
        'notice_id' => $notice->id,
        'location_id' => $locationId,
    ]);
}

function adminUser(array $attributes = []): User
{
    $user = User::factory()->create($attributes);

    Role::query()->firstOrCreate([
        'name' => Role::SUPER_ADMIN,
    ]);

    $user->assignRole(Role::SUPER_ADMIN);

    return $user;
}

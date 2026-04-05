<?php

use App\Models\User;
use App\Models\Image;
use App\Models\Notice;
use App\Models\Setting;
use App\Models\ContactMessage;
use App\Models\NoticeLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('soft deletes a user and excludes it from default queries', function () {
    $user = User::factory()->create();

    $user->delete();

    expect(User::find($user->id))->toBeNull();
    expect(User::withTrashed()->find($user->id))->not->toBeNull();
    expect(User::withTrashed()->find($user->id)->deleted_at)->not->toBeNull();
});

it('restores a soft-deleted user', function () {
    $user = User::factory()->create();
    $user->delete();

    $user->restore();

    expect(User::find($user->id))->not->toBeNull();
    expect(User::find($user->id)->deleted_at)->toBeNull();
});

it('soft deletes a notice', function () {
    $notice = Notice::factory()->create();

    $notice->delete();

    expect(Notice::find($notice->id))->toBeNull();
    expect(Notice::withTrashed()->find($notice->id)->deleted_at)->not->toBeNull();
});

it('soft deletes an image', function () {
    $image = Image::factory()->create();

    $image->delete();

    expect(Image::find($image->id))->toBeNull();
    expect(Image::withTrashed()->find($image->id)->deleted_at)->not->toBeNull();
});

it('soft deletes a contact message', function () {
    $message = ContactMessage::factory()->create();

    $message->delete();

    expect(ContactMessage::find($message->id))->toBeNull();
    expect(ContactMessage::withTrashed()->find($message->id)->deleted_at)->not->toBeNull();
});

it('soft deletes a setting', function () {
    $setting = Setting::create(['key' => 'test_key', 'value' => 'test_value']);

    $setting->delete();

    expect(Setting::find($setting->id))->toBeNull();
    expect(Setting::withTrashed()->find($setting->id)->deleted_at)->not->toBeNull();
});

it('soft deletes a notice location', function () {
    $notice = Notice::factory()->create();
    $location = NoticeLocation::create([
        'notice_id' => $notice->id,
        'location_type' => 'portal',
        'location_code' => '33-A',
    ]);

    $location->delete();

    expect(NoticeLocation::find($location->id))->toBeNull();
    expect(NoticeLocation::withTrashed()->find($location->id)->deleted_at)->not->toBeNull();
});

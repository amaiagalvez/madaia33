<?php

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\NoticeDocument;
use Illuminate\Support\Facades\Storage;

it('serves a public notice document without authentication and records the download', function () {
    Storage::fake('public');

    Storage::disk('public')->put('notice-documents/public-file.pdf', 'dummy-content');

    $document = NoticeDocument::factory()->create([
        'token' => (string) Str::uuid(),
        'filename' => 'public-file.pdf',
        'path' => 'notice-documents/public-file.pdf',
        'mime_type' => 'application/pdf',
        'is_public' => true,
    ]);

    $response = test()->get(route('notice-documents.download', ['token' => $document->token]));

    $response->assertSuccessful()
        ->assertHeader('content-type', 'application/pdf');

    expect($document->downloads()->count())->toBe(1)
        ->and($document->downloads()->first()?->user_id)->toBeNull();
});

it('redirects guests to login for private notice documents', function () {
    Storage::fake('public');

    Storage::disk('public')->put('notice-documents/private-file.pdf', 'dummy-content');

    $document = NoticeDocument::factory()->create([
        'token' => (string) Str::uuid(),
        'filename' => 'private-file.pdf',
        'path' => 'notice-documents/private-file.pdf',
        'mime_type' => 'application/pdf',
        'is_public' => false,
    ]);

    $response = test()->get(route('notice-documents.download', ['token' => $document->token]));

    $response->assertRedirect(route('login'));

    expect($document->downloads()->count())->toBe(0);
});

it('allows authenticated users to download private notice documents and records their user id', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    Storage::disk('public')->put('notice-documents/auth-file.pdf', 'dummy-content');

    $document = NoticeDocument::factory()->create([
        'token' => (string) Str::uuid(),
        'filename' => 'auth-file.pdf',
        'path' => 'notice-documents/auth-file.pdf',
        'mime_type' => 'application/pdf',
        'is_public' => false,
    ]);

    $response = test()->actingAs($user)->get(route('notice-documents.download', ['token' => $document->token]));

    $response->assertSuccessful();

    expect($document->downloads()->count())->toBe(1)
        ->and($document->downloads()->first()?->user_id)->toBe($user->id);
});

it('returns 404 for an invalid notice document token', function () {
    test()->get(route('notice-documents.download', ['token' => 'missing-token']))
        ->assertNotFound();
});

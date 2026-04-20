<?php

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\NoticeDocument;
use Illuminate\Support\Facades\Storage;

it('tracks guest and authenticated document downloads', function () {
    Storage::fake('public');

    $fileName = 'tracking-' . Str::random(6) . '.pdf';
    Storage::disk('public')->put('notice-documents/' . $fileName, 'dummy-content');

    $document = NoticeDocument::factory()->create([
        'token' => (string) Str::uuid(),
        'filename' => $fileName,
        'path' => 'notice-documents/' . $fileName,
        'mime_type' => 'application/pdf',
        'is_public' => true,
    ]);

    test()->get(route('notice-documents.download', ['token' => $document->token]))
        ->assertSuccessful();

    $user = User::factory()->create();

    test()->actingAs($user)
        ->get(route('notice-documents.download', ['token' => $document->token]))
        ->assertSuccessful();

    expect($document->downloads()->count())->toBe(2)
        ->and($document->downloads()->whereNotNull('user_id')->count())->toBe(1)
        ->and($document->downloads()->whereNull('user_id')->count())->toBe(1);
})->repeat(2);

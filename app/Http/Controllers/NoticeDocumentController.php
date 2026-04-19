<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NoticeDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class NoticeDocumentController extends Controller
{
    public function show(Request $request, string $token): BinaryFileResponse|RedirectResponse
    {
        $document = NoticeDocument::query()->where('token', $token)->firstOrFail();

        if (! $document->is_public && $request->user() === null) {
            return redirect()->route('login');
        }

        $document->downloads()->create([
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'downloaded_at' => now(),
        ]);

        return response()->download(
            Storage::disk('public')->path($document->path),
            $document->filename,
            ['Content-Type' => $document->mime_type],
        );
    }
}

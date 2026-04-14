<?php

namespace App\Http\Controllers\Messaging;

use Illuminate\Http\Request;
use App\Models\CampaignDocument;
use App\Models\CampaignRecipient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\CampaignTrackingEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class TrackingController extends Controller
{
    public function open(string $token): Response
    {
        $recipient = CampaignRecipient::query()->where('tracking_token', $token)->first();

        if ($recipient === null) {
            abort(404);
        }

        CampaignTrackingEvent::query()->create([
            'campaign_recipient_id' => $recipient->id,
            'campaign_document_id' => null,
            'event_type' => 'open',
            'url' => null,
            'ip_address' => request()->ip(),
        ]);

        $pixel = base64_decode('R0lGODlhAQABAIABAP///wAAACwAAAAAAQABAAACAkQBADs=', true);

        return response($pixel ?: '', 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    public function click(string $token, Request $request): RedirectResponse
    {
        $recipient = CampaignRecipient::query()->where('tracking_token', $token)->first();

        if ($recipient === null) {
            abort(404);
        }

        $destinationUrl = (string) $request->query('url', '/');

        CampaignTrackingEvent::query()->create([
            'campaign_recipient_id' => $recipient->id,
            'campaign_document_id' => null,
            'event_type' => 'click',
            'url' => $destinationUrl,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->away($destinationUrl);
    }

    public function document(string $token, CampaignDocument $document): Response|RedirectResponse
    {
        $recipient = CampaignRecipient::query()->where('tracking_token', $token)->first();

        if ($recipient === null || $recipient->campaign_id !== $document->campaign_id) {
            abort(404);
        }

        if (! $document->is_public && ! Auth::check()) {
            return redirect()->guest('/login');
        }

        CampaignTrackingEvent::query()->create([
            'campaign_recipient_id' => $recipient->id,
            'campaign_document_id' => $document->id,
            'event_type' => 'download',
            'url' => null,
            'ip_address' => request()->ip(),
        ]);

        return response()->download(Storage::disk('public')->path($document->path), $document->filename);
    }
}

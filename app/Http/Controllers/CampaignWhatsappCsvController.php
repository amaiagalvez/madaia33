<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Services\WhatsappMessageBuilder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CampaignWhatsappCsvController extends Controller
{
  public function __invoke(
    Request $request,
    Campaign $campaign,
    WhatsappMessageBuilder $builder,
  ): StreamedResponse {
    abort_unless($request->user()?->can('view', $campaign), 403);
    abort_unless($campaign->channel === 'whatsapp', 404);

    $campaign->load(['recipients.owner', 'recipients.trackingEvents', 'documents']);

    $pending = $campaign->recipients
      ->filter(fn(CampaignRecipient $r): bool => ! $r->trackingEvents->contains('event_type', 'whatsapp_sent'));

    $filename = 'whatsapp-campana-' . $campaign->id . '.csv';

    return response()->streamDownload(function () use ($pending, $campaign, $builder): void {
      $stream = fopen('php://output', 'w');

      if ($stream === false) {
        return;
      }

      fwrite($stream, "\xEF\xBB\xBF");
      fputcsv($stream, [__('campaigns.admin.csv.phone'), __('campaigns.admin.csv.message')], ';');

      foreach ($pending as $recipient) {
        fputcsv($stream, [$recipient->contact, $builder->build($campaign, $recipient)], ';');
      }

      fclose($stream);
    }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
  }
}

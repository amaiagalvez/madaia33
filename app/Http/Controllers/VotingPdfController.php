<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\SupportedLocales;
use App\Services\VotingPdfBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VotingPdfController extends Controller
{
  public function adminDelegated(Request $request, VotingPdfBuilder $builder): StreamedResponse
  {
    $this->ensureAdminAccess($request->user());

    return $this->downloadPdf($builder, 'delegated', $request->user());
  }

  public function adminInPerson(Request $request, VotingPdfBuilder $builder): StreamedResponse
  {
    $this->ensureAdminAccess($request->user());

    return $this->downloadPdf($builder, 'in_person', $request->user());
  }

  public function publicDelegated(Request $request, VotingPdfBuilder $builder): StreamedResponse
  {
    $this->ensurePublicAccess($request->user());

    return $this->downloadPdf($builder, 'delegated', $request->user());
  }

  public function publicInPerson(Request $request, VotingPdfBuilder $builder): StreamedResponse
  {
    $this->ensurePublicAccess($request->user());

    return $this->downloadPdf($builder, 'in_person', $request->user());
  }

  private function downloadPdf(VotingPdfBuilder $builder, string $type, ?User $user): StreamedResponse
  {
    $payload = $builder->build($type);

    $pdf = Pdf::loadView('pdf.votings.ballot', $payload)
      ->setPaper('a4');

    $filename = $this->localizedFilename($type, $user);

    return response()->streamDownload(
      static function () use ($pdf): void {
        echo $pdf->output();
      },
      $filename,
      ['Content-Type' => 'application/pdf']
    );
  }

  private function localizedFilename(string $type, ?User $user): string
  {
    $locale = SupportedLocales::normalize($user?->language ?? app()->getLocale());
    $translationKey = $type === 'in_person'
      ? 'votings.pdf.filename_in_person'
      : 'votings.pdf.filename_delegated';

    $baseName = (string) __($translationKey, [], $locale);
    $slug = Str::slug($baseName);

    if ($slug === '') {
      $slug = 'votings';
    }

    return sprintf('%s-%s.pdf', $slug, now()->format('Ymd-His'));
  }

  private function ensureAdminAccess(?User $user): void
  {
    abort_unless($user instanceof User, 403);

    abort_unless($user->hasAnyRole([
      Role::SUPER_ADMIN,
      Role::GENERAL_ADMIN,
      Role::COMMUNITY_ADMIN,
    ]), 403);
  }

  private function ensurePublicAccess(?User $user): void
  {
    abort_unless($user instanceof User, 403);

    abort_unless(
      $user->canVoteInVotings()
        || $user->canUseDelegatedVoting()
        || $user->isSuperadmin()
        || $user->hasAnyRole([Role::GENERAL_ADMIN, Role::COMMUNITY_ADMIN]),
      403
    );
  }
}

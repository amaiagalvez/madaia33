<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\SupportedLocales;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\VotingPdfBuilder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VotingPdfController extends Controller
{
  public function adminDelegated(Request $request, VotingPdfBuilder $builder): StreamedResponse
  {
    $this->ensureAdminAccess($request->user());

    return $this->downloadPdf(
      $builder,
      'delegated',
      $request->user(),
      $this->selectedVotingIdsFromRequest($request)
    );
  }

  public function adminInPerson(Request $request, VotingPdfBuilder $builder): StreamedResponse
  {
    $this->ensureAdminAccess($request->user());

    return $this->downloadPdf(
      $builder,
      'in_person',
      $request->user(),
      $this->selectedVotingIdsFromRequest($request)
    );
  }

  public function adminDelegatedSequential(Request $request, VotingPdfBuilder $builder): StreamedResponse
  {
    $this->ensureAdminAccess($request->user());

    return $this->downloadPdf(
      $builder,
      'delegated_sequential',
      $request->user(),
      $this->selectedVotingIdsFromRequest($request)
    );
  }

  public function adminInPersonSequential(Request $request, VotingPdfBuilder $builder): StreamedResponse
  {
    $this->ensureAdminAccess($request->user());

    return $this->downloadPdf(
      $builder,
      'in_person_sequential',
      $request->user(),
      $this->selectedVotingIdsFromRequest($request)
    );
  }

  public function adminResults(Request $request, VotingPdfBuilder $builder): StreamedResponse
  {
    $this->ensureAdminAccess($request->user());

    $selectedVotingIds = $this->selectedVotingIdsFromRequest($request);
    $payload = $builder->buildResults($selectedVotingIds);
    $pdf = Pdf::loadView('pdf.votings.results', $payload)->setPaper('a4');
    $filename = $this->localizedFilename('results', $request->user());

    return response()->streamDownload(
      static function () use ($pdf): void {
        echo $pdf->output();
      },
      $filename,
      ['Content-Type' => 'application/pdf']
    );
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

  /**
   * @param  array<int, int>  $selectedVotingIds
   */
  private function downloadPdf(
    VotingPdfBuilder $builder,
    string $type,
    ?User $user,
    array $selectedVotingIds = []
  ): StreamedResponse {
    $isSequential = str_ends_with($type, '_sequential');
    $baseType = $isSequential ? substr($type, 0, -strlen('_sequential')) : $type;
    $view = $isSequential ? 'pdf.votings.ballot-sequential' : 'pdf.votings.ballot';

    $payload = $builder->build($baseType, $selectedVotingIds);
    $pdf = Pdf::loadView($view, $payload)->setPaper('a4');
    $filename = $this->localizedFilename($type, $user);

    return response()->streamDownload(
      static function () use ($pdf): void {
        echo $pdf->output();
      },
      $filename,
      ['Content-Type' => 'application/pdf']
    );
  }

  /**
   * @return array<int, int>
   */
  private function selectedVotingIdsFromRequest(Request $request): array
  {
    $rawIds = $request->query('voting_ids', []);

    if (! is_array($rawIds)) {
      $rawIds = [$rawIds];
    }

    return collect($rawIds)
      ->map(static fn(mixed $id): int => (int) $id)
      ->filter(static fn(int $id): bool => $id > 0)
      ->unique()
      ->values()
      ->all();
  }

  private function localizedFilename(string $type, User $user): string
  {
    $locale = SupportedLocales::normalize($user->language);
    $translationKey = match ($type) {
      'in_person' => 'votings.pdf.filename_in_person',
      'in_person_sequential' => 'votings.pdf.filename_in_person_sequential',
      'delegated_sequential' => 'votings.pdf.filename_delegated_sequential',
      'results' => 'votings.pdf.filename_results',
      default => 'votings.pdf.filename_delegated',
    };

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

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
        $payload = $builder->build($type, $selectedVotingIds);

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
            ->map(static fn (mixed $id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
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

<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Notice;
use App\Models\Voting;
use App\Models\Setting;
use App\Models\Construction;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class PublicHomeController extends Controller
{
    public function index(): View
    {
        $historyImageUrls = $this->historyImageUrls();
        $latestNotices = $this->latestUntaggedNotices();
        $generalNotices = $this->generalNotices($latestNotices);
        $locationNotices = $this->locationNotices($latestNotices);
        $frontPrimaryEmail = Setting::stringValue('front_primary_email', '');
        $activeConstructions = $this->activeConstructions();

        return view('public.home', [
            'historyImageUrls' => $historyImageUrls,
            'historySummary' => Setting::localizedString('home_history_text', __('home.history_summary')),
            'frontPrimaryEmail' => $frontPrimaryEmail,
            'photoRequestText' => Setting::localizedString('front_photo_request_text', __('home.history_photos_summary', ['email' => $frontPrimaryEmail])),
            'generalNotices' => $generalNotices,
            'locationNotices' => $locationNotices,
            'showViewAllButton' => $latestNotices->count() > 6,
            'hasOpenVotings' => Voting::query()->publishedOpen()->exists(),
            'activeConstructions' => $activeConstructions,
            'hasActiveConstructions' => $activeConstructions->isNotEmpty(),
            'votingsWithResults' => $this->votingsWithResults(),
            'latestVotingWithResults' => $this->latestVotingWithResults(),
        ]);
    }

    /**
     * @return Collection<int, string>
     */
    private function historyImageUrls(): Collection
    {
        $historyImageUrls = Image::query()
            ->where('tag', Image::TAG_HISTORY)
            ->oldest()
            ->limit(3)
            ->get()
            ->map(fn (Image $image): string => Storage::url($image->path))
            ->values();

        if ($historyImageUrls->isNotEmpty()) {
            return $historyImageUrls;
        }

        return collect([asset('apple-touch-icon.png')]);
    }

    /**
     * @return Collection<int, Notice>
     */
    private function latestUntaggedNotices(): Collection
    {
        return Notice::public()
            ->whereNull('notice_tag_id')
            ->with('locations.location')
            ->latest()
            ->limit(12)
            ->get();
    }

    /**
     * @param  Collection<int, Notice>  $latestNotices
     * @return Collection<int, Notice>
     */
    private function generalNotices(Collection $latestNotices): Collection
    {
        return $latestNotices
            ->filter(fn (Notice $notice) => $notice->locations->isEmpty())
            ->take(6)
            ->values();
    }

    /**
     * @param  Collection<int, Notice>  $latestNotices
     * @return Collection<int, Notice>
     */
    private function locationNotices(Collection $latestNotices): Collection
    {
        return $latestNotices
            ->filter(fn (Notice $notice) => $notice->locations->isNotEmpty())
            ->take(6)
            ->values();
    }

    /**
     * @return Collection<int, Construction>
     */
    private function activeConstructions(): Collection
    {
        return Construction::query()
            ->active()
            ->orderBy('starts_at')
            ->get(['title', 'slug']);
    }

    /**
     * @return Collection<int, Voting>
     */
    private function votingsWithResults(): Collection
    {
        return Voting::query()
            ->where('show_results', true)
            ->orderByDesc('ends_at')
            ->get(['id', 'name_eu', 'name_es']);
    }

    private function latestVotingWithResults(): ?Voting
    {
        return Voting::query()
            ->where('show_results', true)
            ->orderByDesc('ends_at')
            ->first(['id', 'name_eu', 'name_es']);
    }
}

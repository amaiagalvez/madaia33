<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Notice;
use App\Models\Voting;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class PublicHomeController extends Controller
{
    public function index(): View
    {
        $historyImageUrls = Image::where('tag', Image::TAG_HISTORY)
            ->oldest()
            ->limit(3)
            ->get()
            ->map(fn(Image $image): string => Storage::url($image->path))
            ->values();

        if ($historyImageUrls->isEmpty()) {
            $historyImageUrls = collect([asset('apple-touch-icon.png')]);
        }

        $latestNotices = Notice::public()
            ->with('locations')
            ->latest()
            ->limit(12)
            ->get();

        $generalNotices = $latestNotices
            ->filter(fn(Notice $notice) => $notice->locations->isEmpty())
            ->take(6)
            ->values();

        $locationNotices = $latestNotices
            ->filter(fn(Notice $notice) => $notice->locations->isNotEmpty())
            ->take(6)
            ->values();

        $historySummary = Setting::localizedString('home_history_text', __('home.history_summary'));
        $frontPrimaryEmail = Setting::stringValue('front_primary_email', 'info@madaia33.eus');
        $photoRequestText = Setting::localizedString('front_photo_request_text', __('home.history_photos_summary', ['email' => $frontPrimaryEmail]));

        return view('public.home', [
            'historyImageUrls' => $historyImageUrls,
            'historySummary' => $historySummary,
            'frontPrimaryEmail' => $frontPrimaryEmail,
            'photoRequestText' => $photoRequestText,
            'generalNotices' => $generalNotices,
            'locationNotices' => $locationNotices,
            'showViewAllButton' => $latestNotices->count() > 6,
            'hasOpenVotings' => Voting::query()->publishedOpen()->exists(),
        ]);
    }
}

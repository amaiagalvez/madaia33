<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Notice;
use App\Models\Setting;
use App\SupportedLocales;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class PublicHomeController extends Controller
{
    public function index(): View
    {
        $historyImage = Image::query()
            ->where('tag', Image::TAG_HISTORY)
            ->latest()
            ->first();

        $historyImageUrl =
            $historyImage && Storage::disk('public')->exists($historyImage->path)
            ? Storage::url($historyImage->path)
            : asset('apple-touch-icon.png');

        $latestNotices = Notice::public()
            ->with('locations')
            ->latest()
            ->limit(12)
            ->get();

        $generalNotices = $latestNotices
            ->filter(fn (Notice $notice) => $notice->locations->isEmpty())
            ->take(6)
            ->values();

        $locationNotices = $latestNotices
            ->filter(fn (Notice $notice) => $notice->locations->isNotEmpty())
            ->take(6)
            ->values();

        $historyTextKeys = SupportedLocales::localizedKeys('home_history_text');
        $historyTextSettings = Setting::query()
            ->whereIn('key', $historyTextKeys)
            ->pluck('value', 'key');

        $historySummary = __('home.history_summary');

        foreach ($historyTextKeys as $historyTextKey) {
            if ($historyTextSettings->has($historyTextKey)) {
                $historySummary = (string) $historyTextSettings[$historyTextKey];

                break;
            }
        }

        return view('public.home', [
            'historyImageUrl' => $historyImageUrl,
            'historySummary' => $historySummary,
            'generalNotices' => $generalNotices,
            'locationNotices' => $locationNotices,
            'showViewAllButton' => $latestNotices->count() > 6,
        ]);
    }
}

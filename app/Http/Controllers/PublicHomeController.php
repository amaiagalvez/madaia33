<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Notice;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class PublicHomeController extends Controller
{
  public function index(): View
  {
    $historyImage = Image::latest()->first();
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
      ->filter(fn(Notice $notice) => $notice->locations->isEmpty())
      ->take(6)
      ->values();

    $locationNotices = $latestNotices
      ->filter(fn(Notice $notice) => $notice->locations->isNotEmpty())
      ->take(6)
      ->values();

    return view('public.home', [
      'historyImageUrl' => $historyImageUrl,
      'generalNotices' => $generalNotices,
      'locationNotices' => $locationNotices,
      'showViewAllButton' => $latestNotices->count() > 6,
    ]);
  }
}

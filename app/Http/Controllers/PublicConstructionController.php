<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\Construction;
use Illuminate\Contracts\View\View;

class PublicConstructionController extends Controller
{
    public function index(): View
    {
        return view('public.constructions.index', [
            'constructions' => Construction::query()
                ->active()
                ->orderBy('starts_at')
                ->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $construction = Construction::query()
            ->active()
            ->with('tag')
            ->where('slug', $slug)
            ->firstOrFail();

        $notices = Notice::query()
            ->with('documents')
            ->public()
            ->when($construction->tag !== null, function ($query) use ($construction): void {
                $query->whereBelongsTo($construction->tag, 'tag');
            }, function ($query): void {
                $query->whereRaw('1 = 0');
            })
            ->orderByDesc('published_at')
            ->get();

        return view('public.constructions.show', [
            'construction' => $construction,
            'notices' => $notices,
        ]);
    }
}

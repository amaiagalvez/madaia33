<?php

namespace App\Observers;

use App\Models\NoticeTag;
use App\Models\Construction;

class ConstructionObserver
{
    public function created(Construction $construction): void
    {
        $this->syncTag($construction);
    }

    public function updated(Construction $construction): void
    {
        if (! $construction->wasChanged(['title', 'slug'])) {
            return;
        }

        $this->syncTag($construction, $construction->getOriginal('slug'));
    }

    private function syncTag(Construction $construction, ?string $previousSlug = null): void
    {
        $tag = null;

        if (filled($previousSlug)) {
            $tag = NoticeTag::query()->where('slug', $previousSlug)->first();
        }

        if ($tag === null) {
            $tag = NoticeTag::query()->where('slug', $construction->slug)->first();
        }

        if ($tag === null) {
            NoticeTag::query()->create([
                'slug' => $construction->slug,
                'name_eu' => $construction->title,
                'name_es' => $construction->title,
            ]);

            return;
        }

        $tag->fill([
            'slug' => $construction->slug,
            'name_eu' => $construction->title,
            'name_es' => $construction->title,
        ])->save();
    }
}

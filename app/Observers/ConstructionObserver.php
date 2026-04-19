<?php

namespace App\Observers;

use App\Models\NoticeTag;
use App\Models\Construction;

class ConstructionObserver
{
    public function created(Construction $construction): void
    {
        NoticeTag::query()->firstOrCreate(
            ['slug' => 'obra-' . $construction->slug],
            [
                'name_eu' => 'Obra: ' . $construction->title,
                'name_es' => 'Obra: ' . $construction->title,
            ],
        );
    }
}

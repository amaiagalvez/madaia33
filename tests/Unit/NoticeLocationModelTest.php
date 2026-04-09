<?php

use App\Models\Notice;
use App\Models\NoticeLocation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

it('defines expected model configuration and relation for notice locations', function () {
    $model = new NoticeLocation;

    expect($model->timestamps)->toBeFalse()
        ->and($model->getFillable())->toBe([
            'notice_id',
            'location_type',
            'location_code',
        ]);

    $relation = $model->notice();

    expect($relation)->toBeInstanceOf(BelongsTo::class)
        ->and($relation->getRelated())->toBeInstanceOf(Notice::class);
});

<?php

use App\Models\Notice;
use App\Models\Location;
use App\Models\Property;
use App\Models\NoticeLocation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

it('defines expected model configuration and relation for notice locations', function () {
    $model = new NoticeLocation;

    expect($model->timestamps)->toBeFalse()
        ->and($model->getFillable())->toBe([
            'notice_id',
            'location_id',
            'property_id',
        ]);

    $relation = $model->notice();

    expect($relation)->toBeInstanceOf(BelongsTo::class)
        ->and($relation->getRelated())->toBeInstanceOf(Notice::class);

    expect($model->location())->toBeInstanceOf(BelongsTo::class)
        ->and($model->location()->getRelated())->toBeInstanceOf(Location::class);

    expect($model->property())->toBeInstanceOf(BelongsTo::class)
        ->and($model->property()->getRelated())->toBeInstanceOf(Property::class);
});

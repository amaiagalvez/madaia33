<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoticeLocation extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'notice_id',
        'location_id',
        'property_id',
    ];

    /**
     * @return BelongsTo<Notice, $this>
     */
    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Virtual compatibility attribute used in views/tests.
     */
    protected function locationCode(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->relationLoaded('location') && $this->location !== null) {
                return $this->location->code;
            }

            if ($this->relationLoaded('property') && $this->property !== null) {
                if ($this->property->relationLoaded('location') && $this->property->location !== null) {
                    return $this->property->location->code;
                }
            }

            return $this->location?->code ?? $this->property?->location?->code;
        });
    }

    /**
     * Virtual compatibility attribute used in views/tests.
     */
    protected function locationType(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->relationLoaded('location') && $this->location !== null) {
                return $this->location->type;
            }

            if ($this->relationLoaded('property') && $this->property !== null) {
                if ($this->property->relationLoaded('location') && $this->property->location !== null) {
                    return $this->property->location->type;
                }
            }

            return $this->location?->type ?? $this->property?->location?->type;
        });
    }
}

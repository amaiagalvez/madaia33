<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string|null $location_code
 * @property-read string|null $location_type
 */
class NoticeLocation extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'notice_id',
        'location_id',
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
     * Virtual compatibility attribute used in views/tests.
     */
    public function getLocationCodeAttribute(): ?string
    {
        if ($this->relationLoaded('location') && $this->location !== null) {
            return $this->location->code;
        }

        return $this->location?->code;
    }

    /**
     * Virtual compatibility attribute used in views/tests.
     */
    public function getLocationTypeAttribute(): ?string
    {
        if ($this->relationLoaded('location') && $this->location !== null) {
            return $this->location->type;
        }

        return $this->location?->type;
    }
}

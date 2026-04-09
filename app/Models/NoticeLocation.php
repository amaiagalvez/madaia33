<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoticeLocation extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'notice_id',
        'location_type',
        'location_code',
    ];

    /**
     * @return BelongsTo<Notice, $this>
     */
    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class);
    }
}

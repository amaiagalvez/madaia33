<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $notice_id
 * @property int $owner_id
 * @property int|null $user_id
 * @property string|null $ip_address
 * @property Carbon|null $opened_at
 * @property Carbon|null $deleted_at
 */
class NoticeRead extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'notice_id',
        'owner_id',
        'user_id',
        'ip_address',
        'opened_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'notice_id' => 'integer',
            'owner_id' => 'integer',
            'user_id' => 'integer',
            'opened_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Notice, $this>
     */
    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class);
    }

    /**
     * @return BelongsTo<Owner, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

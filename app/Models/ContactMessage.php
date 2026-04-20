<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'user_id',
        'notice_tag_id',
        'subject',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'notice_tag_id' => 'integer',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    protected static function newFactory(): ContactMessageFactory
    {
        return ContactMessageFactory::new();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<NoticeTag, $this>
     */
    public function noticeTag(): BelongsTo
    {
        return $this->belongsTo(NoticeTag::class, 'notice_tag_id');
    }

    /**
     * @return HasOne<MessageReply, $this>
     */
    public function reply(): HasOne
    {
        return $this->hasOne(MessageReply::class);
    }
}

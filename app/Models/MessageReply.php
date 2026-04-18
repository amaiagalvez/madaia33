<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Database\Factories\MessageReplyFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MessageReply extends Model
{
    /** @use HasFactory<MessageReplyFactory> */
    use HasFactory;

    protected $fillable = [
        'contact_message_id',
        'reply_body',
        'sent_at',
    ];

    protected $casts = [
        'contact_message_id' => 'integer',
        'sent_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<ContactMessage, $this>
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(ContactMessage::class, 'contact_message_id');
    }
}

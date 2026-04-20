<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoticeDocumentDownload extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'notice_document_id',
        'user_id',
        'ip_address',
        'downloaded_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'notice_document_id' => 'integer',
            'user_id' => 'integer',
            'downloaded_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<NoticeDocument, $this>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(NoticeDocument::class, 'notice_document_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

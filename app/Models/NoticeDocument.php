<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\NoticeDocumentFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NoticeDocument extends Model
{
    /** @use HasFactory<NoticeDocumentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'notice_id',
        'token',
        'filename',
        'path',
        'mime_type',
        'size_bytes',
        'is_public',
    ];

    protected static function newFactory(): NoticeDocumentFactory
    {
        return NoticeDocumentFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'notice_id' => 'integer',
            'size_bytes' => 'integer',
            'is_public' => 'boolean',
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
     * @return HasMany<NoticeDocumentDownload, $this>
     */
    public function downloads(): HasMany
    {
        return $this->hasMany(NoticeDocumentDownload::class);
    }

    protected static function booted(): void
    {
        static::creating(function (NoticeDocument $document): void {
            if (blank($document->token)) {
                $document->token = (string) Str::uuid();
            }
        });
    }
}

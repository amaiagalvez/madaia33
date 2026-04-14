<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Database\Factories\CampaignDocumentFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CampaignDocument extends Model
{
    /** @use HasFactory<CampaignDocumentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'campaign_id',
        'filename',
        'path',
        'mime_type',
        'size_bytes',
        'is_public',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'campaign_id' => 'integer',
            'size_bytes' => 'integer',
            'is_public' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Campaign, $this>
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    protected static function newFactory(): CampaignDocumentFactory
    {
        return CampaignDocumentFactory::new();
    }
}

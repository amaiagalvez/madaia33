<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Database\Factories\CampaignTrackingEventFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CampaignTrackingEvent extends Model
{
    /** @use HasFactory<CampaignTrackingEventFactory> */
    use HasFactory;

    protected $fillable = [
        'campaign_recipient_id',
        'campaign_document_id',
        'event_type',
        'url',
        'ip_address',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'campaign_recipient_id' => 'integer',
            'campaign_document_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<CampaignRecipient, $this>
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(CampaignRecipient::class, 'campaign_recipient_id');
    }

    /**
     * @return BelongsTo<CampaignDocument, $this>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(CampaignDocument::class, 'campaign_document_id');
    }

    protected static function newFactory(): CampaignTrackingEventFactory
    {
        return CampaignTrackingEventFactory::new();
    }
}

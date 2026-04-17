<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Database\Factories\CampaignRecipientFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CampaignRecipient extends Model
{
    /** @use HasFactory<CampaignRecipientFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'campaign_id',
        'owner_id',
        'slot',
        'contact',
        'message_subject',
        'message_body',
        'tracking_token',
        'status',
        'sent_at',
        'sent_by_user_id',
        'error_message',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'campaign_id' => 'integer',
            'owner_id' => 'integer',
            'sent_at' => 'datetime',
            'sent_by_user_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Campaign, $this>
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
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
    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    /**
     * @return HasMany<CampaignTrackingEvent, $this>
     */
    public function trackingEvents(): HasMany
    {
        return $this->hasMany(CampaignTrackingEvent::class);
    }

    protected static function newFactory(): CampaignRecipientFactory
    {
        return CampaignRecipientFactory::new();
    }
}

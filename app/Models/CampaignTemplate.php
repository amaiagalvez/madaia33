<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Database\Factories\CampaignTemplateFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CampaignTemplate extends Model
{
    /** @use HasFactory<CampaignTemplateFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'subject_eu',
        'subject_es',
        'body_eu',
        'body_es',
        'channel',
        'created_by_user_id',
        'location_id',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    protected static function newFactory(): CampaignTemplateFactory
    {
        return CampaignTemplateFactory::new();
    }
}

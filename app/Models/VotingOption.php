<?php

namespace App\Models;

use App\Models\Concerns\ResolvesLocalizedAttributes;
use Database\Factories\VotingOptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VotingOption extends Model
{
    /** @use HasFactory<VotingOptionFactory> */
    use HasFactory, ResolvesLocalizedAttributes, SoftDeletes;

    protected $fillable = [
        'voting_id',
        'label_eu',
        'label_es',
        'position',
    ];

    /**
     * @return BelongsTo<Voting, $this>
     */
    public function voting(): BelongsTo
    {
        return $this->belongsTo(Voting::class);
    }

    public function getLabelAttribute(): string
    {
        return $this->resolveLocalizedAttribute('label');
    }
}

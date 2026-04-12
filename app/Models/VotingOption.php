<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Database\Factories\VotingOptionFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\ResolvesLocalizedAttributes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

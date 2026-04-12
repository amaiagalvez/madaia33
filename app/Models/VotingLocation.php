<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Database\Factories\VotingLocationFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VotingLocation extends Model
{
    /** @use HasFactory<VotingLocationFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'voting_id',
        'location_id',
    ];

    /**
     * @return BelongsTo<Voting, $this>
     */
    public function voting(): BelongsTo
    {
        return $this->belongsTo(Voting::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}

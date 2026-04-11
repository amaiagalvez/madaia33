<?php

namespace App\Models;

use Database\Factories\VotingBallotFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VotingBallot extends Model
{
    /** @use HasFactory<VotingBallotFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'voting_id',
        'owner_id',
        'cast_by_user_id',
        'cast_ip_address',
        'cast_latitude',
        'cast_longitude',
        'cast_delegate_dni',
        'is_in_person',
        'voted_at',
    ];

    protected function casts(): array
    {
        return [
            'voted_at' => 'datetime',
            'cast_latitude' => 'float',
            'cast_longitude' => 'float',
            'is_in_person' => 'bool',
        ];
    }

    /**
     * @return BelongsTo<Voting, $this>
     */
    public function voting(): BelongsTo
    {
        return $this->belongsTo(Voting::class);
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
    public function castByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cast_by_user_id');
    }

    /**
     * @return HasMany<VotingSelection, $this>
     */
    public function selections(): HasMany
    {
        return $this->hasMany(VotingSelection::class);
    }
}

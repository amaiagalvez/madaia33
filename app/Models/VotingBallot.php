<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VotingBallot extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'voting_id',
        'owner_id',
        'cast_by_user_id',
        'voted_at',
    ];

    protected function casts(): array
    {
        return [
            'voted_at' => 'datetime',
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

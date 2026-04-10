<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VotingSelection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'voting_id',
        'voting_ballot_id',
        'owner_id',
        'voting_option_id',
    ];

    /**
     * @return BelongsTo<Voting, $this>
     */
    public function voting(): BelongsTo
    {
        return $this->belongsTo(Voting::class);
    }

    /**
     * @return BelongsTo<VotingBallot, $this>
     */
    public function ballot(): BelongsTo
    {
        return $this->belongsTo(VotingBallot::class, 'voting_ballot_id');
    }

    /**
     * @return BelongsTo<Owner, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    /**
     * @return BelongsTo<VotingOption, $this>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(VotingOption::class, 'voting_option_id');
    }
}

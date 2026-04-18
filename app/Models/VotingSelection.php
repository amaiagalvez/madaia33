<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Database\Factories\VotingSelectionFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VotingSelection extends Model
{
    /** @use HasFactory<VotingSelectionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'voting_id',
        'voting_ballot_id',
        'owner_id',
        'pct_total',
        'voting_option_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pct_total' => 'decimal:4',
        ];
    }

    protected static function newFactory(): VotingSelectionFactory
    {
        return VotingSelectionFactory::new();
    }

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

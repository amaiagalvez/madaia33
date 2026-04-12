<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Database\Factories\VotingOptionTotalFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VotingOptionTotal extends Model
{
    /** @use HasFactory<VotingOptionTotalFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'voting_id',
        'voting_option_id',
        'votes_count',
        'pct_total',
    ];

    protected function casts(): array
    {
        return [
            'pct_total' => 'decimal:4',
        ];
    }

    protected static function newFactory(): VotingOptionTotalFactory
    {
        return VotingOptionTotalFactory::new();
    }

    /**
     * @return BelongsTo<Voting, $this>
     */
    public function voting(): BelongsTo
    {
        return $this->belongsTo(Voting::class);
    }

    /**
     * @return BelongsTo<VotingOption, $this>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(VotingOption::class, 'voting_option_id');
    }
}

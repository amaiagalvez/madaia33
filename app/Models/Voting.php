<?php

namespace App\Models;

use Database\Factories\VotingFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\ResolvesLocalizedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Voting extends Model
{
    /** @use HasFactory<VotingFactory> */
    use HasFactory, ResolvesLocalizedAttributes, SoftDeletes;

    protected $fillable = [
        'name_eu',
        'name_es',
        'question_eu',
        'question_es',
        'starts_at',
        'ends_at',
        'is_published',
        'is_anonymous',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_published' => 'boolean',
            'is_anonymous' => 'boolean',
        ];
    }

    /**
     * @return HasMany<VotingOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(VotingOption::class)->orderBy('position');
    }

    /**
     * @return HasMany<VotingLocation, $this>
     */
    public function locations(): HasMany
    {
        return $this->hasMany(VotingLocation::class);
    }

    /**
     * @return HasMany<VotingBallot, $this>
     */
    public function ballots(): HasMany
    {
        return $this->hasMany(VotingBallot::class);
    }

    /**
     * @return HasMany<VotingSelection, $this>
     */
    public function selections(): HasMany
    {
        return $this->hasMany(VotingSelection::class);
    }

    /**
     * @return HasMany<VotingOptionTotal, $this>
     */
    public function optionTotals(): HasMany
    {
        return $this->hasMany(VotingOptionTotal::class);
    }

    /**
     * @param  Builder<Voting>  $query
     * @return Builder<Voting>
     */
    public function scopeCurrent(Builder $query): Builder
    {
        $today = today();

        return $query
            ->whereDate('starts_at', '<=', $today)
            ->whereDate('ends_at', '>=', $today);
    }

    /**
     * @param  Builder<Voting>  $query
     * @return Builder<Voting>
     */
    public function scopePublishedOpen(Builder $query): Builder
    {
        return $query
            ->current()
            ->where('is_published', true);
    }

    public function isOpen(): bool
    {
        $today = today();

        return $this->starts_at !== null
            && $this->ends_at !== null
            && $this->starts_at->lte($today)
            && $this->ends_at->gte($today);
    }

    public function hasLocationRestrictions(): bool
    {
        return $this->locations()->exists();
    }

    public function getNameAttribute(): string
    {
        return $this->resolveLocalizedAttribute('name');
    }

    public function getQuestionAttribute(): string
    {
        return $this->resolveLocalizedAttribute('question');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Database\Factories\ConstructionFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Construction extends Model
{
    /** @use HasFactory<ConstructionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected static function newFactory(): ConstructionFactory
    {
        return ConstructionFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return HasOne<NoticeTag, $this>
     */
    public function tag(): HasOne
    {
        return $this->hasOne(NoticeTag::class, 'slug', 'slug');
    }

    /**
     * @param  Builder<Construction>  $query
     * @return Builder<Construction>
     */
    public function scopeActive(Builder $query): Builder
    {
        $today = today();

        return $query
            ->whereDate('starts_at', '<=', $today)
            ->where(function (Builder $query) use ($today): void {
                $query->whereDate('ends_at', '>=', $today)
                    ->orWhereNull('ends_at');
            })
            ->where('is_active', true);
    }
}

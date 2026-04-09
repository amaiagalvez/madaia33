<?php

namespace App\Models;

use Database\Factories\NoticeFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\ResolvesLocalizedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notice extends Model
{
    /** @use HasFactory<NoticeFactory> */
    use HasFactory;

    use ResolvesLocalizedAttributes;
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'title_eu',
        'title_es',
        'content_eu',
        'content_es',
        'is_public',
        'published_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * @return HasMany<NoticeLocation, $this>
     */
    public function locations(): HasMany
    {
        return $this->hasMany(NoticeLocation::class);
    }

    /**
     * Scope to only public notices.
     *
     * @param  Builder<Notice>  $query
     * @return Builder<Notice>
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Bilingual accessor for title with fallback.
     */
    public function getTitleAttribute(): string
    {
        return $this->resolveLocalizedAttribute('title');
    }

    /**
     * Bilingual accessor for content with fallback.
     */
    public function getContentAttribute(): string
    {
        return $this->resolveLocalizedAttribute('content');
    }
}

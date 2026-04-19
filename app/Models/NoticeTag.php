<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Database\Factories\NoticeTagFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\ResolvesLocalizedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NoticeTag extends Model
{
    /** @use HasFactory<NoticeTagFactory> */
    use HasFactory, ResolvesLocalizedAttributes, SoftDeletes;

    protected $fillable = [
        'slug',
        'name_eu',
        'name_es',
    ];

    protected static function newFactory(): NoticeTagFactory
    {
        return NoticeTagFactory::new();
    }

    /**
     * @return HasMany<Notice, $this>
     */
    public function notices(): HasMany
    {
        return $this->hasMany(Notice::class);
    }

    public function getNameAttribute(): string
    {
        return $this->resolveLocalizedAttribute('name');
    }
}

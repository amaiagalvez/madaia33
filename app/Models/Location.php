<?php

namespace App\Models;

use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Location extends Model
{
    /** @use HasFactory<LocationFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'name',
    ];

    protected static function newFactory(): LocationFactory
    {
        return LocationFactory::new();
    }

    /**
     * @return HasMany<Property, $this>
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function managedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Scope to portals only.
     *
     * @param  Builder<Location>  $query
     * @return Builder<Location>
     */
    public function scopePortals(Builder $query): Builder
    {
        return $query->where('type', 'portal');
    }

    /**
     * Scope to locals only.
     *
     * @param  Builder<Location>  $query
     * @return Builder<Location>
     */
    public function scopeLocals(Builder $query): Builder
    {
        return $query->where('type', 'local');
    }

    /**
     * Scope to garages only.
     *
     * @param  Builder<Location>  $query
     * @return Builder<Location>
     */
    public function scopeGarages(Builder $query): Builder
    {
        return $query->where('type', 'garage');
    }

    /**
     * Scope to storage rooms only.
     *
     * @param  Builder<Location>  $query
     * @return Builder<Location>
     */
    public function scopeStorage(Builder $query): Builder
    {
        return $query->where('type', 'storage');
    }
}

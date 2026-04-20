<?php

namespace App\Models;

use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'location_id',
        'code',
        'name',
        'community_pct',
        'location_pct',
    ];

    protected function casts(): array
    {
        return [
            'community_pct' => 'decimal:4',
            'location_pct' => 'decimal:4',
        ];
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return HasMany<PropertyAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(PropertyAssignment::class);
    }

    /**
     * @return HasMany<PropertyAssignment, $this>
     */
    public function activeAssignments(): HasMany
    {
        return $this->hasMany(PropertyAssignment::class)->whereNull('end_date');
    }

    public function isAssigned(): bool
    {
        return $this->activeAssignments()->exists();
    }

    public function displayCode(): string
    {
        return trim((string) ($this->code ?: $this->name));
    }

    public function displayLabel(): string
    {
        return '[' . $this->displayCode() . '] ' . $this->name;
    }
}

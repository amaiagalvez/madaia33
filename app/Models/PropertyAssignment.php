<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Database\Factories\PropertyAssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyAssignment extends Model
{
    /** @use HasFactory<PropertyAssignmentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'owner_id',
        'start_date',
        'end_date',
        'admin_validated',
        'owner_validated',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'admin_validated' => 'boolean',
            'owner_validated' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * @return BelongsTo<Owner, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    /**
     * Scope to active (no end date) assignments.
     *
     * @param  Builder<PropertyAssignment>  $query
     * @return Builder<PropertyAssignment>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('end_date');
    }

    public function isActive(): bool
    {
        return $this->end_date === null;
    }
}

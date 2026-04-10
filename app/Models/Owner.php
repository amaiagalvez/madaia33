<?php

namespace App\Models;

use Database\Factories\OwnerFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Owner extends Model
{
    /** @use HasFactory<OwnerFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'coprop1_name',
        'coprop1_dni',
        'coprop1_phone',
        'coprop1_email',
        'coprop2_name',
        'coprop2_dni',
        'coprop2_phone',
        'coprop2_email',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    /**
     * @return HasMany<OwnerAuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(OwnerAuditLog::class);
    }
}

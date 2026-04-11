<?php

namespace App\Models;

use App\SupportedLocales;
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
        'language',
        'coprop2_name',
        'coprop2_dni',
        'coprop2_phone',
        'coprop2_email',
    ];

    /**
     * @var array<string, string>
     */
    protected $attributes = [
        'language' => SupportedLocales::DEFAULT,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'language' => 'string',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $owner): void {
            $user = $owner->user;

            if ($user === null) {
                return;
            }

            $updated = false;

            if ($user->name !== $owner->coprop1_name) {
                $user->name = $owner->coprop1_name;
                $updated = true;
            }

            if ($user->email !== $owner->coprop1_email) {
                $user->email = $owner->coprop1_email;
                $updated = true;
            }

            if ($user->language !== $owner->language) {
                $user->language = $owner->language;
                $updated = true;
            }

            if ($updated) {
                $user->saveQuietly();
            }
        });
    }

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

<?php

namespace App\Models;

use App\SupportedLocales;
use Database\Factories\OwnerFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'coprop1_surname',
        'coprop1_dni',
        'coprop1_phone',
        'coprop1_telegram_id',
        'coprop1_email',
        'language',
        'preferred_locale',
        'accepted_terms_at',
        'coprop2_name',
        'coprop2_surname',
        'coprop2_dni',
        'coprop2_phone',
        'coprop2_telegram_id',
        'coprop2_email',
        'coprop1_email_error_count',
        'coprop1_email_invalid',
        'coprop1_phone_error_count',
        'coprop1_phone_invalid',
        'coprop2_email_error_count',
        'coprop2_email_invalid',
        'coprop2_phone_error_count',
        'coprop2_phone_invalid',
        'last_contact_error_at',
    ];

    /**
     * @var array<string, int|string|bool>
     */
    protected $attributes = [
        'language' => SupportedLocales::DEFAULT,
        'coprop1_email_error_count' => 0,
        'coprop1_email_invalid' => false,
        'coprop1_phone_error_count' => 0,
        'coprop1_phone_invalid' => false,
        'coprop2_email_error_count' => 0,
        'coprop2_email_invalid' => false,
        'coprop2_phone_error_count' => 0,
        'coprop2_phone_invalid' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'language' => 'string',
            'preferred_locale' => 'string',
            'accepted_terms_at' => 'datetime',
            'coprop1_email_error_count' => 'integer',
            'coprop1_email_invalid' => 'boolean',
            'coprop1_phone_error_count' => 'integer',
            'coprop1_phone_invalid' => 'boolean',
            'coprop2_email_error_count' => 'integer',
            'coprop2_email_invalid' => 'boolean',
            'coprop2_phone_error_count' => 'integer',
            'coprop2_phone_invalid' => 'boolean',
            'last_contact_error_at' => 'datetime',
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

    protected static function newFactory(): OwnerFactory
    {
        return OwnerFactory::new();
    }

    /**
     * @return Attribute<string, never>
     */
    protected function fullName1(): Attribute
    {
        return Attribute::make(
            get: fn (): string => trim(
                (string) $this->coprop1_name . ' ' . (string) $this->coprop1_surname,
            ),
        );
    }

    /**
     * @return Attribute<string, never>
     */
    protected function fullName2(): Attribute
    {
        return Attribute::make(
            get: fn (): string => trim(
                (string) $this->coprop2_name . ' ' . (string) $this->coprop2_surname,
            ),
        );
    }
}

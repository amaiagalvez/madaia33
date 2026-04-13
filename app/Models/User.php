<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\SupportedLocales;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Database\Factories\UserFactory;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'email', 'password', 'is_active', 'language', 'delegated_vote_terms_accepted_at'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    /**
     * @var array<string, string>
     */
    protected $attributes = [
        'language' => SupportedLocales::DEFAULT,
    ];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'language' => 'string',
            'delegated_vote_terms_accepted_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * @return HasOne<Owner, $this>
     */
    public function owner(): HasOne
    {
        return $this->hasOne(Owner::class);
    }

    /**
     * @return HasMany<UserLoginSession, $this>
     */
    public function loginSessions(): HasMany
    {
        return $this->hasMany(UserLoginSession::class);
    }

    public function syncOwnerIdentity(): void
    {
        $owner = $this->owner;

        if ($owner === null) {
            return;
        }

        $updated = false;

        if ($owner->coprop1_name !== $this->name) {
            $owner->coprop1_name = $this->name;
            $updated = true;
        }

        if ($owner->coprop1_email !== $this->email) {
            $owner->coprop1_email = $this->email;
            $updated = true;
        }

        if ($owner->language !== $this->language) {
            $owner->language = $this->language;
            $updated = true;
        }

        if ($updated) {
            $owner->saveQuietly();
        }
    }

    /**
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * @return BelongsToMany<Location, $this>
     */
    public function managedLocations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class);
    }

    public function isSuperadmin(): bool
    {
        if ($this->id !== 1) {
            return false;
        }

        return $this->roles()->where('name', Role::SUPER_ADMIN)->exists();
    }

    public function hasRole(string $role): bool
    {
        if ($this->isSuperadmin() && $role === Role::SUPER_ADMIN) {
            return true;
        }

        return $this->roles->contains('name', $role);
    }

    /**
     * @param  array<int, string>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        if ($this->isSuperadmin()) {
            return true;
        }

        return $this->roles
            ->pluck('name')
            ->intersect($roles)
            ->isNotEmpty();
    }

    public function canAccessAdminPanel(): bool
    {
        return $this->hasAnyRole([
            Role::SUPER_ADMIN,
            Role::GENERAL_ADMIN,
            Role::COMMUNITY_ADMIN,
        ]);
    }

    public function canManageUsers(): bool
    {
        return $this->hasAnyRole([
            Role::SUPER_ADMIN,
        ]);
    }

    public function canManageAllLocations(): bool
    {
        return $this->hasAnyRole([
            Role::SUPER_ADMIN,
            Role::GENERAL_ADMIN,
        ]);
    }

    public function canManageLocation(Location $location): bool
    {
        if ($this->canManageAllLocations()) {
            return true;
        }

        return $this->hasRole(Role::COMMUNITY_ADMIN)
            && $this->managedLocations()->whereKey($location->id)->exists();
    }

    public function canManageNotices(): bool
    {
        return $this->hasAnyRole([
            Role::SUPER_ADMIN,
            Role::GENERAL_ADMIN,
            Role::COMMUNITY_ADMIN,
        ]);
    }

    public function canManageAdminVotings(): bool
    {
        return $this->hasAnyRole([
            Role::SUPER_ADMIN,
            Role::GENERAL_ADMIN,
            Role::COMMUNITY_ADMIN,
        ]);
    }

    public function canUseDelegatedVoting(): bool
    {
        return $this->hasRole(Role::DELEGATED_VOTE);
    }

    public function canVoteInVotings(): bool
    {
        if ($this->isSuperadmin()) {
            return false;
        }

        if (! $this->relationLoaded('owner')) {
            $this->loadMissing('owner');
        }

        if ($this->owner === null) {
            return false;
        }

        if (! $this->hasAnyRole([Role::GENERAL_ADMIN, Role::COMMUNITY_ADMIN, Role::DELEGATED_VOTE])) {
            return true;
        }

        return $this->hasRole(Role::PROPERTY_OWNER);
    }

    public function hasOnlyOwnerRole(): bool
    {
        $roles = $this->roleNames();

        return $roles->count() === 1 && $roles->contains(Role::PROPERTY_OWNER);
    }

    /**
     * @param  array<int, string>  $roles
     */
    public function syncRoleNames(array $roles): void
    {
        $normalizedRoles = collect($roles)
            ->filter(static fn(string $role): bool => in_array($role, Role::names(), true))
            ->reject(fn(string $role): bool => $this->isSuperadmin() && $role !== Role::SUPER_ADMIN)
            ->unique()
            ->values();

        if ($this->isSuperadmin() && ! $normalizedRoles->contains(Role::SUPER_ADMIN)) {
            $normalizedRoles->prepend(Role::SUPER_ADMIN);
        }

        $roleIds = Role::query()
            ->whereIn('name', $normalizedRoles->all())
            ->pluck('id')
            ->all();

        $this->roles()->sync($roleIds);
    }

    public function assignRole(string $role): void
    {
        $roleModel = Role::query()->where('name', $role)->first();

        if ($roleModel === null) {
            return;
        }

        if ($this->roles()->whereKey($roleModel->id)->doesntExist()) {
            $this->roles()->attach($roleModel->id);
        }
    }

    /**
     * @return Collection<int, string>
     */
    public function roleNames(): Collection
    {
        return $this->roles->pluck('name')->values();
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(
            (new ResetPasswordNotification($token))
                ->locale(SupportedLocales::normalize(session('locale', app()->getLocale()))),
        );
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\SupportedLocales;
use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['name', 'email', 'password', 'is_active'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

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

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(
            (new ResetPasswordNotification($token))
                ->locale(SupportedLocales::normalize(session('locale', app()->getLocale()))),
        );
    }
}

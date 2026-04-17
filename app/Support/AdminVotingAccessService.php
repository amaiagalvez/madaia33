<?php

namespace App\Support;

use App\Models\Role;
use App\Models\User;
use App\Models\Voting;
use Illuminate\Database\Eloquent\Builder;

class AdminVotingAccessService
{
    public function canManage(?User $user): bool
    {
        return $user?->hasAnyRole([
            Role::SUPER_ADMIN,
            Role::GENERAL_ADMIN,
            Role::COMMUNITY_ADMIN,
        ]) ?? false;
    }

    /**
     * @return Builder<Voting>
     */
    public function queryForUser(User $user): Builder
    {
        $query = Voting::query();

        if ($user->hasRole(Role::SUPER_ADMIN)) {
            return $query;
        }

        if ($user->hasRole(Role::GENERAL_ADMIN)) {
            return $query->whereDoesntHave('locations');
        }

        if ($user->hasRole(Role::COMMUNITY_ADMIN)) {
            $managedLocationIds = $user->managedLocations()
                ->pluck('locations.id')
                ->map(static fn (int $locationId): int => $locationId)
                ->values()
                ->all();

            if ($managedLocationIds === []) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('locations', function (Builder $locationsQuery) use ($managedLocationIds): void {
                $locationsQuery->whereIn('location_id', $managedLocationIds);
            });
        }

        return $query->whereRaw('1 = 0');
    }

    public function canAccess(User $user, Voting $voting): bool
    {
        if ($user->hasRole(Role::SUPER_ADMIN)) {
            return true;
        }

        if ($user->hasRole(Role::GENERAL_ADMIN)) {
            return ! $voting->locations()->exists();
        }

        if (! $user->hasRole(Role::COMMUNITY_ADMIN)) {
            return false;
        }

        $managedLocationIds = $user->managedLocations()
            ->pluck('locations.id')
            ->map(static fn (int $locationId): int => $locationId)
            ->values()
            ->all();

        if ($managedLocationIds === []) {
            return false;
        }

        return $voting->locations()
            ->whereIn('location_id', $managedLocationIds)
            ->exists();
    }
}

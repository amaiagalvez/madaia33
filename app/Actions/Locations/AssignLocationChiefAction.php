<?php

namespace App\Actions\Locations;

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Models\Location;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignLocationChiefAction
{
    /**
     * @throws ValidationException
     */
    public function execute(Location $location, Owner $owner): void
    {
        $this->ensureSupportedLocationType($location);
        $this->ensureOwnerBelongsToLocation($location, $owner);

        $chiefUser = $this->resolveChiefUser($owner);

        DB::transaction(function () use ($location, $chiefUser): void {
            $previousChiefUsers = $this->previousChiefUsersForLocation($location);

            $this->detachLocationFromPreviousChiefUsers($previousChiefUsers, $location);

            $chiefUser->managedLocations()->syncWithoutDetaching([$location->id]);
            $chiefUser->assignRole(Role::COMMUNITY_ADMIN);

            $this->syncPreviousChiefRoles($previousChiefUsers, $chiefUser);
        });
    }

    /**
     * @throws ValidationException
     */
    private function ensureSupportedLocationType(Location $location): void
    {
        if (in_array($location->type, ['portal', 'garage'], true)) {
            return;
        }

        throw ValidationException::withMessages([
            'chiefOwnerId' => __('admin.locations.chief_owner_invalid_type'),
        ]);
    }

    /**
     * @throws ValidationException
     */
    private function ensureOwnerBelongsToLocation(Location $location, Owner $owner): void
    {
        $ownerHasActiveAssignment = $owner->activeAssignments()
            ->whereHas('property', function ($query) use ($location): void {
                $query->where('location_id', $location->id);
            })
            ->exists();

        if ($ownerHasActiveAssignment) {
            return;
        }

        throw ValidationException::withMessages([
            'chiefOwnerId' => __('admin.locations.chief_owner_must_belong_to_location'),
        ]);
    }

    /**
     * @throws ValidationException
     */
    private function resolveChiefUser(Owner $owner): User
    {
        $chiefUser = $owner->user;

        if ($chiefUser instanceof User) {
            return $chiefUser;
        }

        throw ValidationException::withMessages([
            'chiefOwnerId' => __('admin.locations.chief_owner_without_user'),
        ]);
    }

    /**
     * @return Collection<int, User>
     */
    private function previousChiefUsersForLocation(Location $location): Collection
    {
        return User::query()
            ->whereHas('managedLocations', function ($query) use ($location): void {
                $query->whereKey($location->id);
            })
            ->whereHas('roles', function ($query): void {
                $query->where('name', Role::COMMUNITY_ADMIN);
            })
            ->lockForUpdate()
            ->get();
    }

    /**
     * @param  Collection<int, User>  $previousChiefUsers
     */
    private function detachLocationFromPreviousChiefUsers(Collection $previousChiefUsers, Location $location): void
    {
        foreach ($previousChiefUsers as $previousChiefUser) {
            $previousChiefUser->managedLocations()->detach($location->id);
        }
    }

    /**
     * @param  Collection<int, User>  $previousChiefUsers
     */
    private function syncPreviousChiefRoles(Collection $previousChiefUsers, User $chiefUser): void
    {
        foreach ($previousChiefUsers as $previousChiefUser) {
            if ($previousChiefUser->id === $chiefUser->id || $previousChiefUser->managedLocations()->exists()) {
                continue;
            }

            $roleNames = $previousChiefUser->roleNames()
                ->reject(static fn (string $name): bool => $name === Role::COMMUNITY_ADMIN)
                ->values()
                ->all();

            $previousChiefUser->syncRoleNames($roleNames);
        }
    }
}

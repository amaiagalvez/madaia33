<?php

namespace App\Actions\Locations;

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignLocationChiefAction
{
  /**
   * @throws ValidationException
   */
  public function execute(Location $location, Owner $owner): void
  {
    if (! in_array($location->type, ['portal', 'garage'], true)) {
      throw ValidationException::withMessages([
        'chiefOwnerId' => __('admin.locations.chief_owner_invalid_type'),
      ]);
    }

    $ownerHasActiveAssignment = $owner->activeAssignments()
      ->whereHas('property', function ($query) use ($location): void {
        $query->where('location_id', $location->id);
      })
      ->exists();

    if (! $ownerHasActiveAssignment) {
      throw ValidationException::withMessages([
        'chiefOwnerId' => __('admin.locations.chief_owner_must_belong_to_location'),
      ]);
    }

    $chiefUser = $owner->user;

    if ($chiefUser === null) {
      throw ValidationException::withMessages([
        'chiefOwnerId' => __('admin.locations.chief_owner_without_user'),
      ]);
    }

    DB::transaction(function () use ($location, $chiefUser): void {
      $previousChiefUsers = User::query()
        ->whereHas('managedLocations', function ($query) use ($location): void {
          $query->whereKey($location->id);
        })
        ->whereHas('roles', function ($query): void {
          $query->where('name', Role::COMMUNITY_ADMIN);
        })
        ->lockForUpdate()
        ->get();

      foreach ($previousChiefUsers as $previousChiefUser) {
        $previousChiefUser->managedLocations()->detach($location->id);
      }

      $chiefUser->managedLocations()->syncWithoutDetaching([$location->id]);
      $chiefUser->assignRole(Role::COMMUNITY_ADMIN);

      foreach ($previousChiefUsers as $previousChiefUser) {
        if ($previousChiefUser->id === $chiefUser->id) {
          continue;
        }

        $stillManagesLocations = $previousChiefUser->managedLocations()->exists();

        if ($stillManagesLocations) {
          continue;
        }

        $roleNames = $previousChiefUser->roleNames()
          ->reject(static fn(string $name): bool => $name === Role::COMMUNITY_ADMIN)
          ->values()
          ->all();

        $previousChiefUser->syncRoleNames($roleNames);
      }
    });
  }
}

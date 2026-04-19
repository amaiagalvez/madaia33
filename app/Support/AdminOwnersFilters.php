<?php

namespace App\Support;

use App\Models\Owner;
use Illuminate\Database\Eloquent\Builder;

class AdminOwnersFilters
{
  /**
   * @param  array{status?: string, portal?: string, local?: string, garage?: string, storage?: string, search?: string, ownershipView?: string}  $filters
   * @return Builder<Owner>
   */
    public static function apply(Builder $query, array $filters): Builder
    {
    $status = $filters['status'] ?? 'active';

    if ($status === 'active') {
        $query->whereHas('activeAssignments');
    } elseif ($status === 'inactive') {
        $query->whereDoesntHave('activeAssignments');
    }

    if (($filters['ownershipView'] ?? 'default') === 'without_properties') {
        $query->whereDoesntHave('activeAssignments');
    }

    self::applyLocationFilter($query, $filters['portal'] ?? '', 'portal');
    self::applyLocationFilter($query, $filters['local'] ?? '', 'local');
    self::applyLocationFilter($query, $filters['garage'] ?? '', 'garage');
    self::applyLocationFilter($query, $filters['storage'] ?? '', 'storage');
    self::applySearchFilter($query, $filters['search'] ?? '');

    return $query;
    }

  /**
   * @param  Builder<Owner>  $query
   */
    private static function applyLocationFilter(Builder $query, string $locationId, string $type): void
    {
    if ($locationId === '') {
        return;
    }

    $query->whereHas('activeAssignments.property.location', function (Builder $locationQuery) use ($locationId, $type): void {
        $locationQuery->where('type', $type)->where('id', $locationId);
    });
    }

  /**
   * @param  Builder<Owner>  $query
   */
    private static function applySearchFilter(Builder $query, string $search): void
    {
    $term = trim($search);

    if ($term === '') {
        return;
    }

    $escapedTerm = addcslashes($term, '%_');

    $query->where(function (Builder $searchQuery) use ($escapedTerm, $term): void {
        $like = '%' . $escapedTerm . '%';

        $searchQuery
        ->where('coprop1_name', 'like', $like)
        ->orWhere('coprop1_surname', 'like', $like)
        ->orWhere('coprop1_dni', 'like', $like)
        ->orWhere('coprop1_phone', 'like', $like)
        ->orWhere('coprop1_email', 'like', $like)
        ->orWhere('coprop2_name', 'like', $like)
        ->orWhere('coprop2_surname', 'like', $like)
        ->orWhere('coprop2_dni', 'like', $like)
        ->orWhere('coprop2_phone', 'like', $like)
        ->orWhere('coprop2_email', 'like', $like)
        ->orWhere('language', 'like', $like);

        if (is_numeric($term)) {
        $searchQuery->orWhere('id', (int) $term);
        }
    });
    }
}

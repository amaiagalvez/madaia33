<?php

namespace App\Services\Messaging;

use App\Models\Owner;
use App\Models\Campaign;
use App\Models\CampaignLocation;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class RecipientResolver
{
    /**
     * @return Collection<int, array{owner_id: int, slot: string, contact: string}>
     */
    public function resolve(Campaign $campaign): Collection
    {
        $owners = Owner::query()
            ->whereHas('activeAssignments')
            ->with('activeAssignments.property.location')
            ->where($this->filterByCampaignRecipientFilter($campaign))
            ->get();

        return $owners
            ->flatMap(fn (Owner $owner) => $this->resolveOwnerContactsForChannel($owner, (string) $campaign->channel))
            ->values();
    }

    /**
     * @return \Closure(Builder<Owner>): void
     */
    private function filterByCampaignRecipientFilter(Campaign $campaign): \Closure
    {
        return function (Builder $query) use ($campaign): void {
            $locationIds = $this->locationIdsForCampaign($campaign);

            if ($locationIds === []) {
                return;
            }

            $query->whereHas('activeAssignments.property', function (Builder $propertyQuery) use ($locationIds): void {
                $propertyQuery->whereIn('location_id', $locationIds);
            });
        };
    }

    /**
     * @return array<int, int>
     */
    private function locationIdsForCampaign(Campaign $campaign): array
    {
        if ($campaign->relationLoaded('locations')) {
            return $campaign->locations
                ->filter(static fn (CampaignLocation $location): bool => $location->deleted_at === null)
                ->pluck('location_id')
                ->map(static fn (int $locationId): int => $locationId)
                ->unique()
                ->values()
                ->all();
        }

        if (! $campaign->exists) {
            return [];
        }

        return CampaignLocation::query()
            ->where('campaign_id', $campaign->id)
            ->whereNull('deleted_at')
            ->pluck('location_id')
            ->map(static fn (int $locationId): int => $locationId)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, array{owner_id: int, slot: string, contact: string}>
     *
     * @SuppressWarnings("PHPMD.NPathComplexity")
     */
    private function resolveOwnerContactsForChannel(Owner $owner, string $channel): Collection
    {
        $contactsBySlot = match ($channel) {
            'email' => [
                'coprop1' => $owner->coprop1_email_invalid ? null : $owner->coprop1_email,
                'coprop2' => $owner->coprop2_email_invalid ? null : $owner->coprop2_email,
            ],
            'sms' => [
                'coprop1' => $owner->coprop1_phone_invalid ? null : $owner->coprop1_phone,
                'coprop2' => $owner->coprop2_phone_invalid ? null : $owner->coprop2_phone,
            ],
            'whatsapp' => [
                'coprop1' => $owner->coprop1_phone_invalid || ! $owner->coprop1_has_whatsapp ? null : $owner->coprop1_phone,
                'coprop2' => $owner->coprop2_phone_invalid || ! $owner->coprop2_has_whatsapp ? null : $owner->coprop2_phone,
            ],
            'telegram' => [
                'coprop1' => $owner->coprop1_phone_invalid ? null : $owner->coprop1_telegram_id,
                'coprop2' => $owner->coprop2_phone_invalid ? null : $owner->coprop2_telegram_id,
            ],
            'manual' => [
                'coprop1' => $this->manualChannelContact($owner),
                'coprop2' => null,
            ],
            default => [
                'coprop1' => null,
                'coprop2' => null,
            ],
        };

        return collect($contactsBySlot)
            ->filter(fn (?string $contact): bool => filled($contact))
            ->map(fn (string $contact, string $slot): array => [
                'owner_id' => $owner->id,
                'slot' => $slot,
                'contact' => $contact,
            ])
            ->values();
    }

    private function manualChannelContact(Owner $owner): ?string
    {
        if (filled($owner->coprop1_email)) {
            return null;
        }

        if (! filled($owner->coprop1_phone)) {
            return 'manual';
        }

        if (! $owner->coprop1_has_whatsapp) {
            return 'manual';
        }

        return null;
    }
}

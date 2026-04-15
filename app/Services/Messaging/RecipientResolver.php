<?php

namespace App\Services\Messaging;

use App\Models\Owner;
use App\Models\Campaign;
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
            ->where($this->filterByCampaignRecipientFilter($campaign->recipient_filter))
            ->get();

        return $owners
            ->flatMap(fn (Owner $owner) => $this->resolveOwnerContactsForChannel($owner, (string) $campaign->channel))
            ->values();
    }

    /**
     * @return \Closure(Builder<Owner>): void
     */
    private function filterByCampaignRecipientFilter(string $recipientFilter): \Closure
    {
        return function (Builder $query) use ($recipientFilter): void {
            if ($recipientFilter === 'all') {
                return;
            }

            if (! str_contains($recipientFilter, ':')) {
                return;
            }

            [$type, $code] = explode(':', $recipientFilter, 2);

            if (! in_array($type, ['portal', 'garage'], true)) {
                return;
            }

            $query->whereHas('activeAssignments.property.location', function (Builder $locationQuery) use ($type, $code): void {
                $locationQuery
                    ->where('type', $type)
                    ->where('code', $code);
            });
        };
    }

    /**
     * @return Collection<int, array{owner_id: int, slot: string, contact: string}>
     */
    private function resolveOwnerContactsForChannel(Owner $owner, string $channel): Collection
    {
        $contactsBySlot = match ($channel) {
            'email' => [
                'coprop1' => $owner->coprop1_email_invalid ? null : $owner->coprop1_email,
                'coprop2' => $owner->coprop2_email_invalid ? null : $owner->coprop2_email,
            ],
            'sms', 'whatsapp' => [
                'coprop1' => $owner->coprop1_phone_invalid ? null : $owner->coprop1_phone,
                'coprop2' => $owner->coprop2_phone_invalid ? null : $owner->coprop2_phone,
            ],
            'telegram' => [
                'coprop1' => $owner->coprop1_phone_invalid ? null : $owner->coprop1_telegram_id,
                'coprop2' => $owner->coprop2_phone_invalid ? null : $owner->coprop2_telegram_id,
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
}

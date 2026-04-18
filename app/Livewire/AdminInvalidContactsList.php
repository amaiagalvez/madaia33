<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use Livewire\Component;
use App\Models\Campaign;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class AdminInvalidContactsList extends Component
{
    public function markAsValid(int $ownerId, string $slot, string $channel): void
    {
        $this->authorizeViewAny();

        abort_unless(in_array($slot, ['coprop1', 'coprop2'], true), 403);
        abort_unless(in_array($channel, ['email', 'phone'], true), 403);

        $owner = Owner::query()->findOrFail($ownerId);

        $owner->forceFill([
            $slot . '_' . $channel . '_invalid' => false,
            $slot . '_' . $channel . '_error_count' => 0,
        ])->save();

        if (! $owner->coprop1_email_invalid && ! $owner->coprop1_phone_invalid && ! $owner->coprop2_email_invalid && ! $owner->coprop2_phone_invalid) {
            $owner->forceFill([
                'last_contact_error_at' => null,
            ])->save();
        }

        session()->flash('message', __('general.messages.saved'));
    }

    public function render(): View
    {
        $this->authorizeViewAny();

        return view('livewire.admin.invalid-contacts-list', [
            'rows' => $this->rows(),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rows(): array
    {
        $user = $this->currentUser();
        $query = Owner::query()
            ->where(function ($query): void {
                $query->where('coprop1_email_invalid', true)
                    ->orWhere('coprop1_phone_invalid', true)
                    ->orWhere('coprop2_email_invalid', true)
                    ->orWhere('coprop2_phone_invalid', true);
            });

        if ($user?->hasRole(Role::COMMUNITY_ADMIN)) {
            $managedLocationIds = $user->managedLocations()
                ->pluck('locations.id')
                ->all();

            if ($managedLocationIds === []) {
                return [];
            }

            $query->whereHas('assignments', function (Builder $q) use ($managedLocationIds): void {
                $q->whereNull('end_date')
                    ->whereHas('property', function (Builder $q2) use ($managedLocationIds): void {
                        $q2->whereIn('location_id', $managedLocationIds);
                    });
            });
        }

        $owners = $query->orderByDesc('last_contact_error_at')->get();

        $rows = [];

        foreach ($owners as $owner) {
            foreach (['coprop1', 'coprop2'] as $slot) {
                foreach (['email', 'phone'] as $channel) {
                    $invalidField = $slot . '_' . $channel . '_invalid';
                    $errorCountField = $slot . '_' . $channel . '_error_count';
                    $contactField = $slot . '_' . $channel;
                    $nameField = $slot . '_name';

                    if (! $owner->{$invalidField}) {
                        continue;
                    }

                    $rows[] = [
                        'owner_id' => $owner->id,
                        'name' => $owner->{$nameField} ?: __('campaigns.admin.unknown_owner'),
                        'slot' => $slot,
                        'slot_label' => __('campaigns.admin.' . $slot),
                        'contact' => $owner->{$contactField},
                        'channel' => $channel,
                        'channel_label' => __('campaigns.admin.channels.' . ($channel === 'phone' ? 'sms' : $channel)),
                        'errors' => (int) $owner->{$errorCountField},
                        'last_error_at' => $owner->last_contact_error_at,
                    ];
                }
            }
        }

        return $rows;
    }

    private function authorizeViewAny(): void
    {
        $user = $this->currentUser();

        abort_if($user === null, 403);

        $this->authorize('viewAny', Campaign::class);
    }

    private function currentUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }
}

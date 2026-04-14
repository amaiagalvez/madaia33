<?php

namespace App\Support;

use App\Models\Role;
use App\Models\User;
use App\Models\Location;
use App\Models\CampaignTemplate;

class CampaignAdminOptions
{
    public function __construct(private readonly ?User $user) {}

    public static function forUser(?User $user): self
    {
        return new self($user);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function channelOptions(): array
    {
        return [
            ['value' => 'email', 'label' => __('campaigns.admin.channels.email')],
            ['value' => 'sms', 'label' => __('campaigns.admin.channels.sms')],
            ['value' => 'whatsapp', 'label' => __('campaigns.admin.channels.whatsapp')],
            ['value' => 'telegram', 'label' => __('campaigns.admin.channels.telegram')],
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function templateOptions(): array
    {
        return CampaignTemplate::query()
            ->orderBy('name')
            ->get()
            ->map(static fn (CampaignTemplate $template): array => [
                'value' => (string) $template->id,
                'label' => $template->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function recipientFilterOptions(): array
    {
        $options = [];

        if ($this->user?->hasRole(Role::COMMUNITY_ADMIN) !== true) {
            $options[] = ['value' => 'all', 'label' => __('campaigns.admin.filters.all')];
        }

        $locations = Location::query()
            ->whereIn('type', ['portal', 'garage'])
            ->when($this->user?->hasRole(Role::COMMUNITY_ADMIN), function ($query): void {
                $query->whereIn('id', $this->user?->managedLocations()->pluck('locations.id')->all() ?? []);
            })
            ->orderByRaw("CASE WHEN type = 'portal' THEN 1 WHEN type = 'garage' THEN 2 ELSE 3 END")
            ->orderBy('code')
            ->get();

        foreach ($locations as $location) {
            $options[] = [
                'value' => $location->type . ':' . $location->code,
                'label' => $this->locationLabel($location->type) . ' ' . $location->code,
            ];
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    public function allowedRecipientFilters(): array
    {
        return collect($this->recipientFilterOptions())
            ->pluck('value')
            ->filter(static fn (mixed $value): bool => is_string($value) && $value !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function allowedManagedLocationCodes(): array
    {
        return collect($this->recipientFilterOptions())
            ->pluck('value')
            ->filter(static fn (mixed $value): bool => is_string($value) && str_contains($value, ':'))
            ->map(static fn (string $value): string => explode(':', $value, 2)[1])
            ->values()
            ->all();
    }

    public function defaultRecipientFilter(): string
    {
        return collect($this->recipientFilterOptions())
            ->pluck('value')
            ->first(fn (mixed $value): bool => is_string($value) && $value !== '')
            ?? 'all';
    }

    public function previewText(?string $textEu, ?string $textEs): string
    {
        $base = trim((string) ($textEu ?? '')) !== ''
            ? trim((string) $textEu)
            : trim((string) ($textEs ?? ''));

        $preview = str_replace(
            ['**nombre**', '**propiedad**', '**portal**'],
            ['Izena Abizena', '1A', 'P-33'],
            $base,
        );

        return (string) preg_replace('/\*\*[^*]+\*\*/', '', $preview);
    }

    private function locationLabel(string $locationType): string
    {
        return match ($locationType) {
            'portal' => __('admin.locations.types.portal'),
            'garage' => __('admin.locations.types.garage'),
            default => $locationType,
        };
    }
}

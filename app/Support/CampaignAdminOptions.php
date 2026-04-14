<?php

namespace App\Support;

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
            ->map(static fn(CampaignTemplate $template): array => [
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
        $accessScope = $this->user?->campaignAccessScope() ?? 'none';

        if ($accessScope === 'none') {
            return [];
        }

        $options = [];

        if (in_array($accessScope, ['all-filters', 'all-only'], true)) {
            $options[] = ['value' => 'all', 'label' => __('campaigns.admin.filters.all')];
        }

        if ($accessScope === 'all-only') {
            return $options;
        }

        $managedLocationIds = $accessScope === 'managed-locations'
            ? ($this->user?->managedLocations()->pluck('locations.id')->all() ?? [])
            : null;

        $locations = Location::query()
            ->whereIn('type', ['portal', 'garage'])
            ->when(is_array($managedLocationIds), function ($query) use ($managedLocationIds): void {
                if ($managedLocationIds === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->whereIn('id', $managedLocationIds);
            })
            ->orderByRaw("CASE WHEN type = 'portal' THEN 1 WHEN type = 'garage' THEN 2 ELSE 3 END")
            ->orderBy('code')
            ->get();

        foreach ($locations as $location) {
            $value = $location->type . ':' . $location->code;

            $options[] = [
                'value' => $value,
                'label' => $this->labelForRecipientFilter($value),
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
            ->filter(static fn(mixed $value): bool => is_string($value) && $value !== '')
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
            ->filter(static fn(mixed $value): bool => is_string($value) && str_contains($value, ':'))
            ->map(static fn(string $value): string => explode(':', $value, 2)[1])
            ->values()
            ->all();
    }

    public function defaultRecipientFilter(): string
    {
        $firstAllowedFilter = collect($this->recipientFilterOptions())
            ->pluck('value')
            ->first(fn(mixed $value): bool => is_string($value) && $value !== '');

        if (is_string($firstAllowedFilter) && $firstAllowedFilter !== '') {
            return $firstAllowedFilter;
        }

        return match ($this->user?->campaignAccessScope()) {
            'all-filters', 'all-only' => 'all',
            default => '',
        };
    }

    public function labelForRecipientFilter(?string $filter): string
    {
        $value = trim((string) $filter);

        if ($value === '' || $value === 'all') {
            return __('campaigns.admin.filters.all');
        }

        if (! str_contains($value, ':')) {
            return $value;
        }

        [$type, $code] = explode(':', $value, 2);

        return $this->locationLabel($type) . ' ' . $code;
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
            'portal' => __('campaigns.admin.filters.portal'),
            'local' => __('campaigns.admin.filters.local'),
            'garage' => __('campaigns.admin.filters.garage'),
            'storage' => __('campaigns.admin.filters.storage'),
            default => $locationType,
        };
    }
}

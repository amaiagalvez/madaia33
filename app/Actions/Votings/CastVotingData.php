<?php

namespace App\Actions\Votings;

final class CastVotingData
{
    public function __construct(
        public readonly string $ipAddress,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?string $delegateDni = null,
        public readonly bool $isInPerson = false,
    ) {}

    public static function fromInputs(
        ?string $ipAddress = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $delegateDni = null,
        bool $isInPerson = false,
    ): self {
        return new self(
            ipAddress: $ipAddress ?? (string) request()->ip(),
            latitude: $latitude,
            longitude: $longitude,
            delegateDni: $delegateDni,
            isInPerson: $isInPerson,
        );
    }
}

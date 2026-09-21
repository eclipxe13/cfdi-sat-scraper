<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Integration;

use DateTimeImmutable;
use JsonSerializable;

class RepositoryItem implements JsonSerializable
{
    private readonly string $uuid;

    private readonly string $type;

    private readonly string $state;

    public function __construct(string $uuid, private readonly DateTimeImmutable $date, string $state, string $type)
    {
        $this->uuid = strtolower($uuid);
        $this->type = strtoupper(substr($type, 0, 1));
        $this->state = strtoupper(substr($state, 0, 1));
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getDownloadType(): string
    {
        return $this->type;
    }

    public function getState(): string
    {
        return $this->state;
    }

    /** @return array{uuid: string, type: string, state: string, date: DateTimeImmutable} */
    public function jsonSerialize(): array
    {
        return [
            'uuid' => $this->uuid,
            'type' => $this->type,
            'state' => $this->state,
            'date' => $this->date,
        ];
    }
}

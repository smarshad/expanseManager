<?php

namespace App\DTOs;

class BackupRequestDTO
{
    public function __construct(
        public readonly array $tables,
        public readonly ?string $from,
        public readonly ?string $to,
        public readonly ?int $createdBy,
        public readonly int $requestedBy
    ) {}

    public static function fromArray(array $data, int $userId): self
    {
        return new self(
            tables: $data['tables'],
            from: $data['from'] ?? null,
            to: $data['to'] ?? null,
            createdBy: $data['created_by'] ?? null,
            requestedBy: $userId
        );
    }
}

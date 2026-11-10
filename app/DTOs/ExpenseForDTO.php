<?php

namespace App\DTOs;

readonly class ExpenseForDTO
{
    public function __construct(
        public string $name,
        public readonly ?int $created_by,
        public ?string $description,
    ) {}

    public static function fromArray(array $data):self{
        return new self(
            name : $data['name'],
            description : $data['description'] ?? NULL,
            created_by : $data['created_by'] ?? NULL,
        );
    }

    public function toArray(): array{
        return [
            'name' => $this->name,
            'description' => $this->description,
            'created_by' => $this->created_by
        ];
    }
}

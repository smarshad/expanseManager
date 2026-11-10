<?php

namespace App\DTOs;

readonly class CategoryDTO
{
    public function __construct(
        public string $name,
        public readonly ?int $created_by,
        public ?int $parent_id = null,
        public ?string $color_code = null,
        public ?string $description = null,
        public int $sort_order = 0,
        public string $budget_type = 'soft_budget',
        public ?float $monthly_budget = null,
        public string $nature = 'need',
        public bool $is_active = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            created_by: $data['created_by'] ?? null,
            parent_id: $data['parent_id'] ?? null,
            color_code: $data['color_code'] ?? null,
            description: $data['description'] ?? null,
            sort_order: (int) ($data['sort_order'] ?? 0),
            budget_type: $data['budget_type'] ?? 'soft_budget',
            monthly_budget: isset($data['monthly_budget']) && $data['monthly_budget'] !== '' ? (float) $data['monthly_budget'] : null,
            nature: $data['nature'] ?? 'need',
            is_active: (bool) ($data['is_active'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'color_code' => $this->color_code,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'budget_type' => $this->budget_type,
            'monthly_budget' => $this->monthly_budget,
            'nature' => $this->nature,
            'created_by' => $this->created_by,
            'is_active' => $this->is_active,
        ];
    }
}
<?php

namespace App\DTOs;

class RoleDTO
{
    public string $name;
    public ?string $description;
    /** @var int[] */
    public array $permissions;

    public function __construct(
        string $name,
        ?string $description,
        array $permissions
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->permissions = $permissions;
    }

    /**
     * Create DTO from array (FormRequest validated data)
     */
    public static function fromArray(array $data): self
    {
        logAction('Role DTO fromArray', 'info', $data);

        return new self(
            $data['name'],
            $data['description'] ?? null,
            $data['permissions']
        );
    }

    /**
     * Convert DTO back to array
     */
    public function toArray(): array
    {
        logAction('Role DTO toArray', 'info', []);

        return [
            'name' => $this->name,
            'description' => $this->description,
            'permissions' => $this->permissions,
        ];
    }
}

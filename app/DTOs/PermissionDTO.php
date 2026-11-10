<?php

namespace App\DTOs;

class PermissionDTO
{
    public function __construct(
        public string $name,
        public ?string $module,
        public ?string $description,
    ){

    }

    public static function fromArray(array $data):self{
        return new self(
            name : $data['name'],
            module : $data['module'],
            description: $data['description:'] ?? NULL,
        );
    }

    public function toArray():array{
        return[
            'name' => $this->name,
            'module' => $this->module,
            'description' => $this->description,
        ];
    }
}

<?php

namespace App\DTOs;

use App\Models\Menu;

class MenuDTO
{
    public function __construct(
        public string $name,
        public ?string $route,
        public ?string $url,
        public ?string $icon,
        public ?int $parent_id,
        public ?int $order,
        public ?int $permission_id,
        public bool $is_active,
        public readonly ?int $created_by = NULL,
    ) {}

    public static function fromArray(array $data):self{
        return new self(
            name : $data['name'],
            route : $data['route'] ?? NULL,
            url : $data['url'] ?? NULL,
            icon : $data['icon'] ?? NULL,
            parent_id : $data['parent_id'] ?? NULL,
            order : $data['order'] ?? NULL,
            permission_id : $data['permission_id'] ?? NULL,
            is_active : $data['is_active'] ?? FALSE,
            created_by: $data['created_by'] ?? NULL,
        );
    }


    public function toArray():array{
        return [
            'name' => $this->name,
            'route' => $this->route,
            'url' => $this->url,
            'icon' => $this->icon,
            'parent_id' => $this->parent_id,
            'order' => $this->order,
            'permission_id' => $this->permission_id,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
        ];
    }

    /**
     * Create DTO from an existing Menu model.
     */
    public static function fromModel(Menu $menu): self
    {
        return new self(
            name: $menu->name,
            route: $menu->route,
            url: $menu->url,
            icon: $menu->icon,
            parent_id: $menu->parent_id,
            order: $menu->order,
            permission_id: $menu->permission_id,
            is_active: $menu->is_active,
            created_by: $menu->created_by,
        );
    }
}

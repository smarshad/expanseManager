<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
class MenuResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        $user = Auth::user();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'route' => $this->route,
            'url' => $this->url,
            'icon' => $this->icon,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'parent_id' => $this->parent_id,
            'parent_name' => $this->whenLoaded('parent', fn() => $this->parent->name),
            'permission' => $this->whenLoaded('permission', fn() => [
                'id' => $this->permission->id,
                'name' => $this->permission->name,
                'module' => $this->permission->module,
            ]),
            'creator'    => $this->whenLoaded('creator', function () {
                return [
                    'name'  => $this->creator->name,
                ];
            }),
            // Recursively load children, useful for nested menu APIs
            'children' => MenuResource::collection($this->whenLoaded('children')),
            'created_at' => $this->created_at->format('d/M/Y H:i:s'),
            'updated_at' => $this->updated_at,
            'can' => $this->when($user,[
                'update' => $user->can('update', $this->resource),
                'delete' => $user->can('delete', $this->resource),
            ]),
        ];
    }
}

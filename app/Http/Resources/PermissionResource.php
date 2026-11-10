<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
class PermissionResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        $user = Auth::user();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'module' => $this->module,
            'guard_name' => $this->guard_name,
            'description' => $this->description,
            'created_at' => $this->created_at->format('d/M/Y H:i:s'),
            'updated_at' => $this->updated_at,
            'can' => $this->when($user,[
                'update' => $user->can('update', $this->resource),
                'delete' => $user->can('delete', $this->resource),
            ]),
        ];
    }
}

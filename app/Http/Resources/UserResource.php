<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = Auth::user();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->roles->pluck('name')->implode(', '),
            'permissions_count' => $this->getAllPermissions()->count(),
            'locked' => $this->is_locked ? 'Yes' : 'No',
            'last_login' => optional($this->last_login_at)?->format('Y-m-d H:i:s') ?? '-',
            'created_at' => $this->created_at->format('Y-m-d'),
            'created_by' => $this->creator?->name ?? '-',
            'can' => $this->when($user, [
                'update' => $user->can('update', $this->resource),
                'delete' => $user->can('delete', $this->resource),
                'viewpermission' => $user->can('viewpermission', $this->resource),
                'editpermission' => $user->can('editpermission', $this->resource),
            ]),
        ];
    }
}

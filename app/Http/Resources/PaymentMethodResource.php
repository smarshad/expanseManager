<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PaymentMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = Auth::user();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'created_by' => $this->created_by,
            // full creator info (only if relation is loaded)
            'creator'    => $this->whenLoaded('creator', function () {
                return [
                    // 'id'    => $this->creator->id,
                    'name'  => $this->creator->name,
                    // 'email' => $this->creator->email,
                ];
            }),
            'can' => $this->when($user, [
                'update' => $user->can('update', $this->resource),
                'delete' => $user->can('delete', $this->resource),
            ]),
        ];
    }
}

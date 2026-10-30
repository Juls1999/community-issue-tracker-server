<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'email' => $this->email,
            'name' => $this->name,

            'role' => $this->whenLoaded(
                'role',
                fn() => [
                    'id' => $this->role?->id,
                    'name' => $this->role?->name
                ]
            ),

            'barangay' => $this->whenLoaded(
                'barangay',
                fn() => [
                    'id' => $this->barangay?->id,
                    'name' => $this->barangay?->name
                ]
            ),
        ];
    }
}
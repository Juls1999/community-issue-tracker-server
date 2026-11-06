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
            'firstName' => $this->first_name,
            'middleName' => $this->middle_name,
            'lastName' => $this->last_name,
            'fullName' => $this->full_name,
            'phoneNumber' => $this->phone_number,
            'isVerified' => $this->is_verified,
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
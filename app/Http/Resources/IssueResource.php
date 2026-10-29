<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IssueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'resolved_at' => $this->resolved_at,

            'reporter' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),

            'assignee' => $this->whenLoaded(
                'assignee',
                fn() =>
                    $this->assignee ? [
                        'id' => $this->assignee->id,
                        'name' => $this->assignee->name,
                    ] : null
            ),

            'category' => $this->whenLoaded(
                'category',
                fn() =>
                    $this->category ? [
                        'id' => $this->category->id,
                        'name' => $this->category->name,
                    ] : null
            ),

            'barangay' => $this->whenLoaded(
                'barangay',
                fn() =>
                    $this->barangay ? [
                        'id' => $this->barangay->id,
                        'name' => $this->barangay->name,
                    ] : null
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

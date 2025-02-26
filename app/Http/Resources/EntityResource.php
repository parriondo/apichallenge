<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'api' => $this->api,
            'description' => $this->description,
            'link' => $this->link,
            'category' => [
                'id' => $this->category->id,
                'category' => $this->category->category
            ]
        ];
    }
}


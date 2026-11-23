<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'ISBN'         => $this->ISBN,
            'author'       => $this->author,
            'publisher'    => $this->publisher,
            'publish_date' => $this->publish_date,
            // Free right now = no open borrowing
            'is_available' => ! $this->customers()
                ->wherePivot('is_returned', false)
                ->exists(),
            // Only included when the controller loaded the relation
            'category'     => CategoryResource::make($this->whenLoaded('category')),
        ];
    }
}
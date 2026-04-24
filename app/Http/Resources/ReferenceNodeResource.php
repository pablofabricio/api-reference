<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReferenceNodeResource extends JsonResource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);

        $data['reference'] = $this->whenLoaded('reference', function () {
            return [
                'id' => $this->reference->id,
                'title' => $this->reference->title,
            ];
        });

        return $data;
    }
}

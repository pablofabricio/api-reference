<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChannelResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'created_by' => $this->created_by,
            'visibility' => $this->visibility,
        ];

        if ($this->relationLoaded('references')) {
            $data['references'] = $this->references->map(function ($ref) {
                return [
                    'id' => $ref->id,
                    'title' => $ref->title,
                    'abbreviation' => $ref->abbreviation,
                    'author' => $ref->author,
                    'description' => $ref->description,
                    'nodes' => $ref->relationLoaded('nodes') ? $ref->nodes->map(function ($n) {
                        return [
                            'id' => $n->id,
                            // use raw attribute to avoid enum cast exceptions when value isn't valid
                            'type' => $n->getAttributes()['type'] ?? $n->type,
                            'label' => $n->label,
                            'content' => $n->content,
                            'parent_node_id' => $n->parent_node_id,
                        ];
                    }) : [],
                ];
            });
        }

        return $data;
    }
}

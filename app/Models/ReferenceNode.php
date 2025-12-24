<?php

namespace App\Models;

use App\Enums\ReferenceNodeType;

class ReferenceNode extends BaseModel
{
    public static function rules(): array
    {
        return [
            'type' => ['required', 'string'],
            'content' => ['required', 'string'],
            'label' => ['nullable', 'string', 'max:255'],
            'reference_id' => ['required', 'integer', 'exists:references,id'],
            'parent_node_id' => ['nullable', 'integer', 'exists:reference_nodes,id'],
            'position' => ['nullable', 'integer'],
        ];
    }
    protected $fillable = [
        'type',
        'content',
        'label',
        'reference_id',
        'parent_node_id',
        'position',
    ];

    protected $casts = [
        'type' => ReferenceNodeType::class,
    ];

    public function reference()
    {
        return $this->belongsTo(Reference::class);
    }

    public function parent()
    {
        return $this->belongsTo(ReferenceNode::class, 'parent_node_id');
    }

    public function children()
    {
        return $this->hasMany(ReferenceNode::class, 'parent_node_id');
    }
}

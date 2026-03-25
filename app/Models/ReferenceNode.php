<?php

namespace App\Models;

use App\Enums\ReferenceNodeType;

class ReferenceNode extends BaseModel
{
    /**
     * Reference trees need more than the default page size.
     * Channel detail expects the full node set for a reference.
     *
     * @var int
     */
    protected $perPage = 300;

    /**
     * Fields allowed as query filters.
     *
     * @var array
     */
    protected static array $filters = [
        'reference_id',
        'parent_node_id',
    ];

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

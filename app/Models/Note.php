<?php

namespace App\Models;

use App\Enums\NoteVisibility;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Model;

class Note extends BaseModel
{
    protected $fillable = [
        'user_id',
        'content',
        'reference_node_id',
        'visibility',
    ];
    
    protected $casts = [
        'visibility' => NoteVisibility::class,
    ];

    /**
     * Fields allowed to be used as filters in requests.
     *
     * @var array
     */
    protected static array $filters = [
        'reference_node_id',
    ];
    
    public static function rules(): array
    {
        $visibilityValues = array_map(fn($e) => $e->value, NoteVisibility::cases());
    
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'content' => ['required', 'string'],
            // require a reference node: notes must be attached to a node
            'reference_node_id' => ['required', 'integer', 'exists:reference_nodes,id'],
            'visibility' => ['required', Rule::in($visibilityValues)],
        ];
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function referenceNode()
    {
        return $this->belongsTo(ReferenceNode::class, 'reference_node_id');
    }
}



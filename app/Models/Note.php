<?php

namespace App\Models;

use App\Enums\NoteVisibility;
use Illuminate\Validation\Rule;

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
    
    public static function rules(): array
    {
        $visibilityValues = array_map(fn($e) => $e->value, NoteVisibility::cases());
    
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'content' => ['required', 'string'],
            'reference_node_id' => ['nullable', 'integer', 'exists:reference_nodes,id'],
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



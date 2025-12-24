<?php

namespace App\Models;

class NoteReferenceAdded extends BaseModel
{
    protected $fillable = [
        'note_id',
        'reference_node_id',
        'user_id',
    ];

    public $timestamps = true;

    public static function rules(): array
    {
        return [
            'note_id' => ['required', 'integer', 'exists:notes,id'],
            'reference_node_id' => ['required', 'integer', 'exists:reference_nodes,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function note()
    {
        return $this->belongsTo(Note::class);
    }

    public function referenceNode()
    {
        return $this->belongsTo(ReferenceNode::class);
    }
}

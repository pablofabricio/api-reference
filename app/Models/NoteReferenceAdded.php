<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;

class NoteReferenceAdded extends BaseModel
{
    protected $table = 'note_reference_added';

    protected $fillable = [
        'note_id',
        'reference_node_id',
        'user_id',
    ];

    public $timestamps = true;

    protected static function booted(): void
    {
        static::creating(function (NoteReferenceAdded $noteReferenceAdded) {
            if (Auth::check()) {
                $noteReferenceAdded->user_id = (int) Auth::id();
            }
        });
    }

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

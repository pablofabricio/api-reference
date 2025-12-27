<?php

namespace App\Repositories;

use App\Models\Note;

class NoteRepository extends BaseRepository
{
    protected function model(): string
    {
        return Note::class;
    }

    /**
     * Paginate notes that belong to a given reference node.
     *
     * @param int $referenceNodeId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateByReferenceNode(int $referenceNodeId)
    {
        return $this->model->where('reference_node_id', $referenceNodeId)->paginate();
    }
}

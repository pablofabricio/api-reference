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

    /**
     * Paginate notes that belong to the authenticated user, preserving model filters.
     */
    public function getPaginateForUser(int $userId)
    {
        $query = $this->model->newQuery()->where('user_id', $userId);

        if (method_exists($this->model, 'filters')) {
            $filters = $this->model::filters();
            foreach ($filters as $field) {
                $value = request()->query($field);
                if (!is_null($value) && $value !== '') {
                    $query->where($field, $value);
                }
            }
        }

        return $query->paginate();
    }
}

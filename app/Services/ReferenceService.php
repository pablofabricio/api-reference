<?php

namespace App\Services;

use App\Repositories\ReferenceRepository;
use Illuminate\Support\Facades\Auth;

class ReferenceService extends BaseService
{
        public function __construct(ReferenceRepository $repository)
        {
                parent::__construct($repository);
        }

        /**
         * Limit index to references owned by the authenticated user.
         */
        protected function paginateConstraints(): array
        {
                return ['user_id' => Auth::id()];
        }
}

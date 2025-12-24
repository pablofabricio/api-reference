<?php

namespace App\Services;

use App\Repositories\LibraryItemRepository;

class LibraryItemService extends BaseService
{
	public function __construct(LibraryItemRepository $repository)
	{
		parent::__construct($repository);
	}
}

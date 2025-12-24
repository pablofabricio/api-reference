<?php

namespace App\Services;

use App\Repositories\LibraryRepository;

class LibraryService extends BaseService
{
	public function __construct(LibraryRepository $repository)
	{
		parent::__construct($repository);
	}
}

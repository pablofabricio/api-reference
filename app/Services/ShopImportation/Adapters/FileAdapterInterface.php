<?php

namespace App\Services\ShopImportation\Adapters;

use Generator;

interface FileAdapterInterface
{
    public function setFilepath(string $filepath): void;

    public function read(): Generator;
}

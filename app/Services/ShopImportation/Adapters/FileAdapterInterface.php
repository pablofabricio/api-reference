<?php

namespace App\Services\ShopImportation\Adapters;

interface FileAdapterInterface
{
    /**
     * Define o caminho do arquivo.
     *
     * @param string $filepath
     */
    public function setFilepath(string $filepath): void;

    /**
     * Lê os dados do arquivo e retorna como um array.
     *
     * @return array
     */
    public function read(): array;
}

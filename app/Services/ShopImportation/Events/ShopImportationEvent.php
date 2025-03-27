<?php

namespace App\Services\ShopImportation\Events;

use App\Services\ShopImportation\Adapters\FileAdapterInterface;

abstract class ShopImportationEvent 
{
    protected string $resource;
    protected array $request;
    protected array $item;
    protected FileAdapterInterface $fileAdapter;

    
    function setRequest(array $request): void
    {
        $this->request = $request;
    }

    function getRequest(): array
    {
        return $this->request;
    }
    
    function getToken(): string
    {
        return $this->request['token'];
    }

    function setItem(array $item): array
    {
        return $this->item = $item;
    }

    function getItem(): array
    {
        return $this->item ?? [];
    }

    function getResource(): string
    {
        return $this->resource;
    }

    function recordsToArray(): array
    {
        return [];
    }
}
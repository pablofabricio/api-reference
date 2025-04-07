<?php

namespace App\Services\ShopImportation\Events;

use App\Services\ShopImportation\Adapters\FileAdapterInterface;
use App\Services\ShopImportation\Enums\EventsStatusEnum;
use Generator;

abstract class ShopImportationEvent 
{
    protected string $resource;
    protected array $request;
    protected array $item;
    protected array $records;
    protected FileAdapterInterface $fileAdapter;
    protected string $status;
    private ?int $currentIndex = null;
    
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

    function getRequestId(): string
    {
        return $this->request['request_id'];
    }

    function setItem(array $item): void
    {
        $this->item = $item;
    }

    function getItem(): array
    {
        return $this->item ?? [];
    }

    function setRecords(array $records): void
    {
        $this->records = $records;
    }

    function getRecords(): array
    {
        return $this->records;
    }

    function setStatus(string $status): void
    {
        $this->status = $status;
    }

    function getStatus(): string
    {
        return $this->status ?? EventsStatusEnum::ON_IMPORT;
    }

    function getResource(): string
    {
        return $this->resource;
    }

    function generateRecords(int $startIndex = 0): Generator
    {
        return yield;
    }

    public function setCurrentIndex(?int $index): void
    {
        $this->currentIndex = $index;
    }

    public function getCurrentIndex(): ?int
    {
        return $this->currentIndex;
    }
}
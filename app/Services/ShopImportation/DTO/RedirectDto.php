<?php 

namespace App\Services\ShopImportation\DTO;

class RedirectDto
{
    public function __construct(
        public string $externalId,
        public string $name
    ) {}

    public static function fromArray(array $item): self
    {
        return new self(
            externalId: $item['brand'],
            name: $item['brand']
        );
    }

    public function toArray(): array
    {
        return [
            'external_id' => $this->externalId,
            'name' => $this->name,
        ];
    }
}

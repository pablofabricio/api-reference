<?php 

namespace App\DTO;

use App\Models\Category;

class CategoryDto
{
    public function __construct(
        public string $externalId,
        public ?int $parentId,
        public string $name
    ) {}

    public static function fromArray(array $category): self
    {
        $parentId = null;
        
        if (!is_null($category['parent'] ?? null)) {
            //$parentId = Category::getByPartnerId($category['parent'])->dooca_id;
        }
        
        return new self(
            externalId: $category['external_id'],
            parentId: $parentId,
            name: $category['name']
        );
    }

    public function toArray(): array
    {
        return [
            'external_id' => $this->externalId,
            'parent_id' => $this->parentId,
            'name' => $this->name,
        ];
    }
}

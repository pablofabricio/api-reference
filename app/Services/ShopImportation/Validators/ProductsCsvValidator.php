<?php

namespace App\Services\ShopImportation\Validators;

class ProductsCsvValidator implements CsvValidatorInterface
{
    protected array $requiredColumns = [
        'product_id',
        'variation_id',
        'name',
        'url',
        'weight',
        'depth',
        'width',
        'height',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'tags',
        'description',
        'short_description',
        'ncm',
        'images',
        'price',
        'price_compare',
        'model',
        'gender',
        'age_group',
        'warranty',
        'active',
        'brand',
        'category_1',
        'category_2',
        'category_3',
        'reference',
        'gtin',
        'mpn',
        'stock',
        'color',
        'variation',
        'variation_price',
        'sku'

    ];

    private array $errors = []; 

    public function validate(array $data): bool
    {
        $isValid = true;

        if (empty($data)) {
            $this->errors[] = 'CSV header is empty or invalid.';
            return false; 
        }

        $header = $data[0] ?? [];

        foreach ($this->requiredColumns as $requiredColumn) {
            if (!in_array($requiredColumn, $header)) {
                $this->errors[] = "Required column '{$requiredColumn}' is missing in the header.";
                $isValid = false; 
            }
        }

        return $isValid; 
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

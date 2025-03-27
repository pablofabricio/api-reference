<?php

namespace App\Services\ShopImportation\Validators;

class ProductsCsvValidator implements CsvValidatorInterface
{
    protected array $requiredColumns = [
        'name', 'url'
    ];

    public function validate(array $data): bool
    {
        if (empty($data)) {
            return false; 
        }

        $header = $data[0] ?? [];

        foreach ($this->requiredColumns as $requiredColumn) {
            if (!in_array($requiredColumn, $header)) {
                return false; 
            }
        }

        return true; 
    }
}

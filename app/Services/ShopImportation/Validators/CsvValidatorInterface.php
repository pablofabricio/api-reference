<?php

namespace App\Services\ShopImportation\Validators;

interface CsvValidatorInterface
{
    public function validate(array $data): bool;
    public function getErrors(): array;
}

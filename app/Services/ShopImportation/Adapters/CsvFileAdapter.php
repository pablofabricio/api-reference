<?php

namespace App\Services\ShopImportation\Adapters;

use App\Services\ShopImportation\Validators\CsvValidatorInterface;
use Illuminate\Support\Facades\Storage;

class CsvFileAdapter implements FileAdapterInterface
{
    protected string $filepath;
    protected CsvValidatorInterface $validator;

    public function __construct(string $filepath, CsvValidatorInterface $validator)
    {
        $this->setFilepath($filepath);
        $this->validator = $validator;
    }

    public function setFilepath(string $filepath): void
    {
        $this->filepath = $filepath;
    }

    public function read(): array
    {
        if (!Storage::disk('local')->exists($this->filepath)) {
            return [];
        }

        $file = Storage::disk('local')->readStream($this->filepath);
        $data = [];

        while (false !== ($row = fgetcsv($file, 0, ',', '"'))) {
            $data[] = $row;
        }

        if (!$this->validator->validate($data)) {
            return []; 
        }

        return $data; 
    }
}

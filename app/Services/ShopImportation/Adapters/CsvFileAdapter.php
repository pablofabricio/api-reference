<?php

namespace App\Services\ShopImportation\Adapters;

use App\Services\ShopImportation\Validators\CsvValidatorInterface;
use Generator;
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

    public function read(): Generator
    {
        if (!Storage::disk('local')->exists($this->filepath)) {
            return; 
        }
    
        $file = Storage::disk('local')->readStream($this->filepath);
        
        if (!$file) {
            return;
        }
    
        $header = fgetcsv($file, 0, ',', '"');
    
        if (!$this->validator->validate([$header])) {
            fclose($file);
            return;
        }
    
        while (($row = fgetcsv($file, 0, ',', '"')) !== false) {
            yield $row;
        }
    
        fclose($file);
    }
}

<?php

namespace App\Services\ShopImportation\Adapters;

use App\Services\ShopImportation\Events\ShopImportationEvent;
use App\Services\ShopImportation\ShopImportationLogger;
use App\Services\ShopImportation\Validators\CsvValidatorInterface;
use Generator;
use Illuminate\Support\Facades\Storage;

class CsvFileAdapter implements FileAdapterInterface
{
    protected string $filepath;
    protected ShopImportationEvent $event;
    protected CsvValidatorInterface $validator;
    protected ShopImportationLogger $logger;

    public function __construct(
        ShopImportationEvent $event, 
        CsvValidatorInterface $validator,
    )
    {
        $this->event = $event;
        $this->setFilepath($event->getRequest()['filePath'] ?? '');
        $this->validator = $validator;
        $this->logger = app(ShopImportationLogger::class);
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
            $this->logger->addError($this->event->getRequestId(), [
                'message' => json_encode($this->validator->getErrors()),
                'resource' => $this->event->getResource(),
            ]);
            return;
        }
    
        while (($row = fgetcsv($file, 0, ',', '"')) !== false) {
            yield $row;
        }
    
        fclose($file);
    }
}

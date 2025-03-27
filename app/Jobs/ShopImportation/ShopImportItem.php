<?php

namespace App\Jobs\ShopImportation;

use App\Services\ShopImportation\Events\ShopImportationEvent;
use App\Services\ShopImportation\Importers\ImporterFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ShopImportItem implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels, InteractsWithQueue;

    protected ShopImportationEvent $event;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ShopImportationEvent $event)
    {
        $this->event = $event; 
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $importer = ImporterFactory::getInstance($this->event->getResource());
        $importer->import($this->event); 
    }
}

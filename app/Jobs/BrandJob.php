<?php

namespace App\Jobs;

use App\Services\BrandMigrateService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BrandJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels, InteractsWithQueue;

    protected array $request;
    protected int $page;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $request, int $page = 1)
    {
        $this->request = $request; 
        $this->page = $page; 
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $service = new BrandMigrateService($this->request, $this->page);
        
        $service->import($this->request);

       //dispatch(new CategoryJob($this->request));
    }
}

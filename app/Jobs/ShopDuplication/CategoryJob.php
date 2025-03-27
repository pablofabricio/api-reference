<?php

namespace App\Jobs\ShopDuplication;

use App\Services\ShopDuplication\CategoryMigrateService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CategoryJob implements ShouldQueue
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
        $service = new CategoryMigrateService($this->request, $this->page);
        
        $service->import($this->request);
    }
}

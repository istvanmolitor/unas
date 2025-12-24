<?php

namespace Molitor\Unas\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Services\UnasProductService;

class DownloadUnasProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public UnasShop $shop
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(UnasProductService $unasProductService): void
    {
        $unasProductService->downloadProducts($this->shop);
    }
}


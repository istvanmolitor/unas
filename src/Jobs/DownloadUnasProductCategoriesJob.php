<?php

declare(strict_types=1);

namespace Molitor\Unas\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Services\UnasProductCategoryService;

class DownloadUnasProductCategoriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public UnasShop $shop
    ) {}

    public function handle(UnasProductCategoryService $unasProductCategoryService): void
    {
        $unasProductCategoryService->repairCategories($this->shop);
    }
}
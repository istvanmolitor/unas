<?php

namespace Molitor\Unas\Console\Commands;

use Illuminate\Console\Command;
use Molitor\Unas\Repositories\UnasShopRepository;
use Molitor\Unas\Repositories\UnasShopRepositoryInterface;
use Molitor\Unas\Services\Dto\Api\UnasProductApiDtoService;
use Molitor\Unas\Services\Dto\UnasProductDtoService;
use Molitor\Unas\Services\UnasProductCategoryApiService;
use Molitor\Unas\Services\UnasProductParameterApiService;
use Molitor\Unas\Services\UnasProductApiService;
use Molitor\Unas\Services\UnasProductService;

class UnasDownloadProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unas:download-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download products from UNAS';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /** @var UnasShopRepository $unasShopRepository */
        $unasShopRepository = app(UnasShopRepositoryInterface::class);

        /** @var UnasProductService $unasProductService */
        $unasProductService = app(UnasProductService::class);

        foreach ($unasShopRepository->getEnableShops() as $shop) {
            $this->info("Downloading products for UNAS shop: {$shop->name}");
            $unasProductService->downloadProducts($shop);
        }
        return 0;
    }
}

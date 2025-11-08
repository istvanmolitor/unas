<?php

namespace Molitor\Unas\Console\Commands;

use Illuminate\Console\Command;
use Molitor\Unas\Repositories\UnasShopRepository;
use Molitor\Unas\Repositories\UnasShopRepositoryInterface;
use Molitor\Unas\Services\UnasProductCategoryService;
use Molitor\Unas\Services\UnasProductParameterService;
use Molitor\Unas\Services\UnasProductService;

class UnasSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unas:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adatok feltöltése az UNAS-ba';

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

        /** @var UnasProductCategoryService $productCategoryService */
        $productCategoryService = app(UnasProductCategoryService::class);

        /** @var UnasProductService $productRepository */
        $productRepository = app(UnasProductService::class);

        /** @var UnasProductParameterService $productParameterService */
        $productParameterService = app(UnasProductParameterService::class);

        foreach ($unasShopRepository->getEnableShops() as $shop) {
            $productRepository->syncDeletes($shop);
            $productCategoryService->syncDeletes($shop);
            $productParameterService->syncDeletes($shop);

            $productParameterService->syncChanges($shop);
            $productCategoryService->syncChanges($shop);
            $productRepository->syncChanges($shop);
        }
        return 0;
    }
}

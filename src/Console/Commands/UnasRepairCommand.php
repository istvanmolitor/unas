<?php

namespace Molitor\Unas\Console\Commands;

use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Models\UnasProductCategory;
use Molitor\Unas\Models\UnasProductCategoryProduct;
use Molitor\Unas\Models\UnasProductParameter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Molitor\Unas\Repositories\UnasOrderRepositoryInterface;
use Molitor\Unas\Repositories\UnasProductCategoryProductRepository;
use Molitor\Unas\Repositories\UnasProductCategoryProductRepositoryInterface;
use Molitor\Unas\Repositories\UnasProductCategoryRepository;
use Molitor\Unas\Repositories\UnasProductCategoryRepositoryInterface;
use Molitor\Unas\Repositories\UnasProductParameterRepository;
use Molitor\Unas\Repositories\UnasProductParameterRepositoryInterface;
use Molitor\Unas\Repositories\UnasProductRepository;
use Molitor\Unas\Repositories\UnasProductRepositoryInterface;
use Molitor\Unas\Repositories\UnasShopRepository;
use Molitor\Unas\Repositories\UnasShopRepositoryInterface;
use Molitor\Unas\Services\UnasProductCategoryService;
use Molitor\Unas\Services\UnasProductParameterService;
use Molitor\Unas\Services\UnasProductService;

class UnasRepairCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unas:repair';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adatok visszahozása az UNAS-ból';

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
        /** @var UnasShopRepository $shopRepository */
        $shopRepository = app(UnasShopRepositoryInterface::class);

        /** @var UnasProductCategoryProductRepository $categoryProductRepository */
        $categoryProductRepository = app(UnasProductCategoryProductRepositoryInterface::class);

        /** @var UnasProductCategoryService $categoryService */
        $categoryService = app(UnasProductCategoryService::class);

        /** @var UnasProductParameterService $parameterService */
        $parameterService = app(UnasProductParameterService::class);

        /** @var UnasProductService $productService */
        $productService = app(UnasProductService::class);

        foreach ($shopRepository->getEnableShops() as $unasShop) {
            $categoryProductRepository->deleteByShop($unasShop);
            $categoryService->repairCategories($unasShop);
            $parameterService->repairParameters($unasShop);
            $productService->repairProducts($unasShop);
        }
        return 0;
    }
}

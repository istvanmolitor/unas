<?php

namespace Molitor\Unas\Console\Commands;

use Illuminate\Console\Command;
use Molitor\Unas\Repositories\UnasShopRepository;
use Molitor\Unas\Repositories\UnasShopRepositoryInterface;
use Molitor\Unas\Services\UnasProductParameterService;

class UnasDownloadProductParameters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unas:download-product-parameters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download product parameters from UNAS';

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

        /** @var UnasProductParameterService $unasProductParameterService */
        $unasProductParameterService = app(UnasProductParameterService::class);

        foreach ($unasShopRepository->getEnableShops() as $shop) {
            $this->info("Downloading product parameters for UNAS shop: {$shop->name}");
            $unasProductParameterService->repairParameters($shop);
        }
        return 0;
    }
}


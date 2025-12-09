<?php

namespace Molitor\Unas\Console\Commands;

use Illuminate\Console\Command;
use Molitor\Unas\Repositories\UnasShopRepositoryInterface;
use Molitor\Unas\Services\UnasOrderService;

class UnasDownloadOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unas:download-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download orders from UNAS for all enabled shops';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var UnasShopRepositoryInterface $unasShopRepository */
        $unasShopRepository = app(UnasShopRepositoryInterface::class);

        /** @var UnasOrderService $unasOrderService */
        $unasOrderService = app(UnasOrderService::class);

        foreach ($unasShopRepository->getEnableShops() as $shop) {
            $this->info("Downloading orders for UNAS shop: {$shop->name}");
            $unasOrderService->downloadOrders($shop);
            $this->info("Finished orders for: {$shop->name}");
        }

        return 0;
    }
}

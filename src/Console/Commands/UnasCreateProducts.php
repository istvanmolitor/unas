<?php

declare(strict_types=1);

namespace Molitor\Unas\Console\Commands;

use Illuminate\Console\Command;
use Molitor\Product\Models\Product;
use Molitor\Product\Repositories\ProductRepositoryInterface;
use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Repositories\UnasProductRepositoryInterface;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Repositories\UnasShopRepositoryInterface;
use Molitor\Unas\Services\ProductCopyService;

class UnasCreateProducts extends Command
{
    private const DIRECTION_MASTER_TO_SHOP = 'master-to-shop';

    private const DIRECTION_SHOP_TO_MASTER = 'shop-to-master';

    protected $signature = 'unas:create-products {--shop-id=} {--direction=}';

    protected $description = 'Copy products between product master and a selected UNAS shop';

    public function __construct(
        private UnasShopRepositoryInterface $unasShopRepository,
        private UnasProductRepositoryInterface $unasProductRepository,
        private ProductRepositoryInterface $productRepository,
        private ProductCopyService $productCopyService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $direction = $this->resolveDirection();
        if (! $direction) {
            return self::FAILURE;
        }

        $shop = $this->resolveShop();

        if (! $shop) {
            return self::FAILURE;
        }

        if ($direction === self::DIRECTION_MASTER_TO_SHOP) {
            return $this->copyMasterToShop($shop);
        }

        return $this->copyShopToMaster($shop);
    }

    private function copyMasterToShop(UnasShop $shop): int
    {
        $copiedProductsCount = 0;

        /** @var Product $product */
        foreach ($this->productRepository->getAll() as $product) {
            $this->productCopyService->copyProduct($product, $shop);
            $copiedProductsCount++;
        }

        $this->info("Copied {$copiedProductsCount} product(s) from product master to shop: {$shop->name}");

        return self::SUCCESS;
    }

    private function copyShopToMaster(UnasShop $shop): int
    {
        $copiedProductsCount = 0;

        /** @var UnasProduct $unasProduct */
        foreach ($this->unasProductRepository->getShopProducts($shop) as $unasProduct) {
            $this->productCopyService->copyUnasProduct($unasProduct);
            $copiedProductsCount++;
        }

        $this->info("Copied {$copiedProductsCount} product(s) from shop {$shop->name} to product master");

        return self::SUCCESS;
    }

    private function resolveDirection(): ?string
    {
        $directionOption = $this->option('direction');
        if (is_string($directionOption) && $directionOption !== '') {
            if (in_array($directionOption, [self::DIRECTION_MASTER_TO_SHOP, self::DIRECTION_SHOP_TO_MASTER], true)) {
                return $directionOption;
            }

            $this->error('Invalid direction. Allowed values: master-to-shop, shop-to-master');

            return null;
        }

        $directionLabels = [
            'Product master -> selected UNAS shop' => self::DIRECTION_MASTER_TO_SHOP,
            'Selected UNAS shop -> product master' => self::DIRECTION_SHOP_TO_MASTER,
        ];

        $selectedLabel = $this->choice(
            'Select copy direction',
            array_keys($directionLabels),
            0
        );

        return $directionLabels[$selectedLabel];
    }

    private function resolveShop(): ?UnasShop
    {
        $shopIdOption = $this->option('shop-id');
        if ($shopIdOption !== null) {
            $shop = $this->unasShopRepository->getById((int) $shopIdOption);
            if (! $shop) {
                $this->error("UNAS shop not found for ID: {$shopIdOption}");
            }

            return $shop;
        }

        $shops = $this->unasShopRepository->getAll();
        if ($shops->isEmpty()) {
            $this->error('No UNAS shops are available.');

            return null;
        }

        $shopChoices = $shops->mapWithKeys(function (UnasShop $shop): array {
            return ["{$shop->name} (#{$shop->id})" => $shop->id];
        })->all();

        $selectedLabel = $this->choice(
            'Select an UNAS shop',
            array_keys($shopChoices),
            0
        );

        return $this->unasShopRepository->getById((int) $shopChoices[$selectedLabel]);
    }
}


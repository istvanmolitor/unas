<?php

declare(strict_types=1);

namespace Molitor\Unas\Console\Commands;

use Illuminate\Console\Command;
use Molitor\Product\Models\ProductField;
use Molitor\Product\Repositories\ProductFieldRepositoryInterface;
use Molitor\Unas\Models\UnasProductParameter;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Repositories\UnasProductParameterRepositoryInterface;
use Molitor\Unas\Repositories\UnasShopRepositoryInterface;

class UnasCopyProductParameters extends Command
{
    private const DIRECTION_MASTER_TO_SHOP = 'master-to-shop';

    private const DIRECTION_SHOP_TO_MASTER = 'shop-to-master';

    protected $signature = 'unas:copy-product-parameters {--shop-id=} {--direction=}';

    protected $description = 'Copy product parameters between product master and a selected UNAS shop';

    public function __construct(
        private UnasShopRepositoryInterface $unasShopRepository,
        private UnasProductParameterRepositoryInterface $unasProductParameterRepository,
        private ProductFieldRepositoryInterface $productFieldRepository,
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
        $copiedParametersCount = 0;

        /** @var ProductField $productField */
        foreach ($this->productFieldRepository->getAll() as $productField) {
            if ($this->unasProductParameterRepository->addToShop($shop, $productField)) {
                $copiedParametersCount++;
            }
        }

        $this->info("Copied {$copiedParametersCount} product parameter(s) from product master to shop: {$shop->name}");

        return self::SUCCESS;
    }

    private function copyShopToMaster(UnasShop $shop): int
    {
        $copiedParametersCount = 0;

        /** @var UnasProductParameter $unasProductParameter */
        foreach ($shop->shopProductParameters()->get() as $unasProductParameter) {
            $productField = $this->productFieldRepository->getByName(
                $unasProductParameter->name,
                $unasProductParameter->language_id
            );

            if ($productField) {
                continue;
            }

            $this->productFieldRepository->create(
                $unasProductParameter->name,
                $unasProductParameter->language_id
            );
            $copiedParametersCount++;
        }

        $this->info("Copied {$copiedParametersCount} product parameter(s) from shop {$shop->name} to product master");

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


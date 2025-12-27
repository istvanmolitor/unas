<?php

namespace Molitor\Unas\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Molitor\Product\Events\ProductUpdateEvent;
use Molitor\Setting\Services\SettingHandlerService;
use Molitor\Unas\Console\Commands\UnasDownloadProducts;
use Molitor\Unas\Console\Commands\UnasDownloadProductCategories;
use Molitor\Unas\Console\Commands\UnasDownloadOrders;
use Molitor\Unas\Console\Commands\UnasRepairCommand;
use Molitor\Unas\Console\Commands\UnasSync;
use Molitor\Unas\Listeners\ProductUpdateListener;
use Molitor\Unas\Repositories\UnasOrderRepository;
use Molitor\Unas\Repositories\UnasOrderRepositoryInterface;
use Molitor\Unas\Repositories\UnasProductAttributeRepository;
use Molitor\Unas\Repositories\UnasProductAttributeRepositoryInterface;
use Molitor\Unas\Repositories\UnasProductCategoryProductRepository;
use Molitor\Unas\Repositories\UnasProductCategoryProductRepositoryInterface;
use Molitor\Unas\Repositories\UnasProductCategoryRepository;
use Molitor\Unas\Repositories\UnasProductCategoryRepositoryInterface;
use Molitor\Unas\Repositories\UnasProductImageRepository;
use Molitor\Unas\Repositories\UnasProductImageRepositoryInterface;
use Molitor\Unas\Repositories\UnasProductParameterRepository;
use Molitor\Unas\Repositories\UnasProductParameterRepositoryInterface;
use Molitor\Unas\Repositories\UnasProductRepository;
use Molitor\Unas\Repositories\UnasProductRepositoryInterface;
use Molitor\Unas\Repositories\UnasShopRepository;
use Molitor\Unas\Repositories\UnasShopRepositoryInterface;
use Molitor\Unas\Services\UnasSettingForm;

class UnasServiceProvider extends EventServiceProvider
{
    protected $listen = [
        ProductUpdateEvent::class => [
            ProductUpdateListener::class,
        ],
    ];

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'unas');

        $this->commands([
            UnasRepairCommand::class,
            UnasSync::class,
            UnasDownloadProducts::class,
            UnasDownloadProductCategories::class,
            UnasDownloadOrders::class,
        ]);

        $this->app->make(SettingHandlerService::class)->register(UnasSettingForm::class);
    }

    public function register()
    {
        $this->app->bind(UnasOrderRepositoryInterface::class, UnasOrderRepository::class);
        $this->app->bind(UnasProductAttributeRepositoryInterface::class, UnasProductAttributeRepository::class);
        $this->app->bind(UnasProductCategoryProductRepositoryInterface::class, UnasProductCategoryProductRepository::class);
        $this->app->bind(UnasProductCategoryRepositoryInterface::class, UnasProductCategoryRepository::class);
        $this->app->bind(UnasProductParameterRepositoryInterface::class, UnasProductParameterRepository::class);
        $this->app->bind(UnasProductRepositoryInterface::class, UnasProductRepository::class);
        $this->app->bind(UnasShopRepositoryInterface::class, UnasShopRepository::class);
        $this->app->bind(UnasProductImageRepositoryInterface::class, UnasProductImageRepository::class);
    }
}

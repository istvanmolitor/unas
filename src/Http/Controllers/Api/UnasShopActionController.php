<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Molitor\Unas\Jobs\DownloadUnasOrdersJob;
use Molitor\Unas\Jobs\DownloadUnasProductCategoriesJob;
use Molitor\Unas\Jobs\DownloadUnasProductParametersJob;
use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Models\UnasProductAttribute;
use Molitor\Unas\Models\UnasProductImage;
use Molitor\Unas\Models\UnasProductTranslation;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Repositories\UnasOrderRepositoryInterface;
use Molitor\Unas\Repositories\UnasProductCategoryProductRepositoryInterface;
use Molitor\Unas\Repositories\UnasProductCategoryRepositoryInterface;
use Molitor\Unas\Repositories\UnasProductParameterRepositoryInterface;
use Molitor\Unas\Repositories\UnasProductRepositoryInterface;
use Molitor\Unas\Services\UnasProductService;

class UnasShopActionController
{
    public function __construct(
        private UnasProductRepositoryInterface $unasProductRepository,
        private UnasProductCategoryRepositoryInterface $unasProductCategoryRepository,
        private UnasProductCategoryProductRepositoryInterface $unasProductCategoryProductRepository,
        private UnasProductParameterRepositoryInterface $unasProductParameterRepository,
        private UnasOrderRepositoryInterface $unasOrderRepository,
    ) {}

    public function downloadProducts(UnasShop $unasShop, UnasProductService $unasProductService): JsonResponse
    {
        $unasProductService->dispatchDownloadProducts($unasShop);

        return response()->json([
            'message' => 'A termékek letöltése elindult.',
        ]);
    }

    public function downloadCategories(UnasShop $unasShop): JsonResponse
    {
        DownloadUnasProductCategoriesJob::dispatch($unasShop);

        return response()->json([
            'message' => 'A termékkategóriák letöltése elindult.',
        ]);
    }

    public function downloadParameters(UnasShop $unasShop): JsonResponse
    {
        DownloadUnasProductParametersJob::dispatch($unasShop);

        return response()->json([
            'message' => 'A paraméterek letöltése elindult.',
        ]);
    }

    public function downloadOrders(UnasShop $unasShop): JsonResponse
    {
        DownloadUnasOrdersJob::dispatch($unasShop);

        return response()->json([
            'message' => 'A megrendelések letöltése elindult.',
        ]);
    }

    public function clearProducts(UnasShop $unasShop): JsonResponse
    {
        $productIds = UnasProduct::withTrashed()
            ->where('unas_shop_id', $unasShop->id)
            ->pluck('id');

        if ($productIds->isNotEmpty()) {
            UnasProductTranslation::whereIn('unas_product_id', $productIds)->delete();
            UnasProductImage::whereIn('unas_product_id', $productIds)->delete();
            UnasProductAttribute::whereIn('unas_product_id', $productIds)->delete();
            DB::table('unas_product_parameter_values')->whereIn('unas_product_id', $productIds)->delete();
        }

        $this->unasProductCategoryProductRepository->deleteByShop($unasShop);
        $this->unasProductRepository->forceDeleteByShop($unasShop);

        return response()->json([
            'message' => 'A termék rekordok véglegesen törölve lettek.',
        ]);
    }

    public function clearCategories(UnasShop $unasShop): JsonResponse
    {
        $this->unasProductCategoryProductRepository->deleteByShop($unasShop);
        $this->unasProductCategoryRepository->forceDeleteByShop($unasShop);

        return response()->json([
            'message' => 'A termékkategória rekordok véglegesen törölve lettek.',
        ]);
    }

    public function clearParameters(UnasShop $unasShop): JsonResponse
    {
        $parameterIds = $unasShop->shopProductParameters()->withTrashed()->pluck('id');

        if ($parameterIds->isNotEmpty()) {
            DB::table('unas_product_parameter_values')->whereIn('unas_product_parameter_id', $parameterIds)->delete();
        }

        $this->unasProductParameterRepository->forceDeleteByShop($unasShop);

        return response()->json([
            'message' => 'A paraméter rekordok véglegesen törölve lettek.',
        ]);
    }

    public function clearOrders(UnasShop $unasShop): JsonResponse
    {
        $this->unasOrderRepository->forceDeleteByShop($unasShop);

        return response()->json([
            'message' => 'A megrendelés rekordok véglegesen törölve lettek.',
        ]);
    }
}
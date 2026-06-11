<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Molitor\Admin\Http\Resources\DataTableResource;
use Molitor\Unas\Http\Requests\StoreUnasProductRequest;
use Molitor\Unas\Http\Requests\UpdateUnasProductRequest;
use Molitor\Unas\Http\Resources\UnasProductResource;
use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Repositories\UnasProductRepositoryInterface;

class UnasProductController
{
    public function __construct(
        private UnasProductRepositoryInterface $unasProductRepository
    ) {}
    public function index(Request $request): JsonResponse
    {
        $query = UnasProduct::query()->with(['shop', 'product', 'productUnit', 'mainImage', 'images', 'translations', 'shopProductCategories']);

        if ($shopId = $request->input('unas_shop_id')) {
            $query->where('unas_shop_id', $shopId);
        }

        if ($search = $request->string('search')->toString()) {
            $query->where('sku', 'like', '%'.$search.'%');
        }

        $allowedSortFields = ['id', 'sku', 'price', 'stock', 'created_at'];
        $sort = $request->input('sort', 'sku');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        if (in_array($sort, $allowedSortFields, true)) {
            $query->orderBy($sort, $direction);
        }

        $products = $query
            ->paginate((int) $request->input('per_page', 10))
            ->withQueryString();

        return response()->json(new DataTableResource(
            $products,
            UnasProductResource::class,
            $request->only(['search', 'sort', 'direction', 'unas_shop_id'])
        ));
    }

    public function store(StoreUnasProductRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $categoryIds = $validated['unas_product_category_ids'] ?? null;
        unset($validated['unas_product_category_ids']);

        $unasProduct = $this->unasProductRepository->create($validated);
        if (is_array($categoryIds)) {
            $unasProduct->shopProductCategories()->sync($categoryIds);
        }
        $unasProduct->setRequestTranslations($validated);
        $unasProduct->save();
        $unasProduct->load(['shop', 'product', 'productUnit', 'mainImage', 'images', 'translations', 'shopProductCategories']);

        return response()->json([
            'data' => new UnasProductResource($unasProduct),
        ], 201);
    }

    public function show(UnasProduct $unasProduct): JsonResponse
    {
        $unasProduct->load(['shop', 'product', 'productUnit', 'mainImage', 'images', 'translations', 'shopProductCategories']);

        return response()->json([
            'data' => new UnasProductResource($unasProduct),
        ]);
    }

    public function update(UpdateUnasProductRequest $request, UnasProduct $unasProduct): JsonResponse
    {
        $validated = $request->validated();
        $categoryIds = $validated['unas_product_category_ids'] ?? null;
        unset($validated['unas_product_category_ids']);

        $unasProduct->update($validated);
        if (is_array($categoryIds)) {
            $unasProduct->shopProductCategories()->sync($categoryIds);
        }
        $unasProduct->setRequestTranslations($validated);
        $unasProduct->save();
        $unasProduct->load(['shop', 'product', 'productUnit', 'mainImage', 'images', 'translations', 'shopProductCategories']);

        return response()->json([
            'data' => new UnasProductResource($unasProduct),
        ]);
    }

    public function destroy(UnasProduct $unasProduct): JsonResponse
    {
        $unasProduct->delete();

        return response()->json(null, 204);
    }
}

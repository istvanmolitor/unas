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

class UnasProductController
{
    public function index(Request $request): JsonResponse
    {
        $query = UnasProduct::query()->with(['shop', 'product']);

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
        $unasProduct = UnasProduct::query()->create($request->validated());

        return response()->json([
            'data' => new UnasProductResource($unasProduct),
        ], 201);
    }

    public function show(UnasProduct $unasProduct): JsonResponse
    {
        $unasProduct->load(['shop', 'product', 'images', 'attributes', 'parameters']);

        return response()->json([
            'data' => new UnasProductResource($unasProduct),
        ]);
    }

    public function update(UpdateUnasProductRequest $request, UnasProduct $unasProduct): JsonResponse
    {
        $unasProduct->update($request->validated());

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

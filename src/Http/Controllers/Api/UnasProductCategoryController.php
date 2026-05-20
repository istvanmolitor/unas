<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Molitor\Admin\Http\Resources\DataTableResource;
use Molitor\Unas\Http\Requests\StoreUnasProductCategoryRequest;
use Molitor\Unas\Http\Requests\UpdateUnasProductCategoryRequest;
use Molitor\Unas\Http\Resources\UnasProductCategoryResource;
use Molitor\Unas\Models\UnasProductCategory;

class UnasProductCategoryController
{
    public function index(Request $request): JsonResponse
    {
        $query = UnasProductCategory::query()->with(['shop', 'parent']);

        if ($shopId = $request->input('unas_shop_id')) {
            $query->where('unas_shop_id', $shopId);
        }

        if ($search = $request->string('search')->toString()) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $allowedSortFields = ['id', 'name', 'created_at'];
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        if (in_array($sort, $allowedSortFields, true)) {
            $query->orderBy($sort, $direction);
        }

        $categories = $query
            ->paginate((int) $request->input('per_page', 10))
            ->withQueryString();

        return response()->json(new DataTableResource(
            $categories,
            UnasProductCategoryResource::class,
            $request->only(['search', 'sort', 'direction', 'unas_shop_id'])
        ));
    }

    public function store(StoreUnasProductCategoryRequest $request): JsonResponse
    {
        $category = UnasProductCategory::query()->create($request->validated());

        return response()->json([
            'data' => new UnasProductCategoryResource($category),
        ], 201);
    }

    public function show(UnasProductCategory $unasProductCategory): JsonResponse
    {
        $unasProductCategory->load(['shop', 'parent', 'childCategories']);

        return response()->json([
            'data' => new UnasProductCategoryResource($unasProductCategory),
        ]);
    }

    public function update(UpdateUnasProductCategoryRequest $request, UnasProductCategory $unasProductCategory): JsonResponse
    {
        $unasProductCategory->update($request->validated());

        return response()->json([
            'data' => new UnasProductCategoryResource($unasProductCategory),
        ]);
    }

    public function destroy(UnasProductCategory $unasProductCategory): JsonResponse
    {
        $unasProductCategory->delete();

        return response()->json(null, 204);
    }
}

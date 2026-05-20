<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Molitor\Admin\Http\Resources\DataTableResource;
use Molitor\Stock\Models\Warehouse;
use Molitor\Unas\Http\Requests\StoreUnasShopRequest;
use Molitor\Unas\Http\Requests\UpdateUnasShopRequest;
use Molitor\Unas\Http\Resources\UnasShopResource;
use Molitor\Unas\Models\UnasShop;

class UnasShopController
{
    public function index(Request $request): JsonResponse
    {
        $query = UnasShop::query()->with(['warehouse']);

        if ($search = $request->string('search')->toString()) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('domain', 'like', '%' . $search . '%');
        }

        $allowedSortFields = ['id', 'name', 'domain', 'enabled', 'created_at'];
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        if (in_array($sort, $allowedSortFields, true)) {
            $query->orderBy($sort, $direction);
        }

        $shops = $query
            ->paginate((int) $request->input('per_page', 10))
            ->withQueryString();

        return response()->json(new DataTableResource(
            $shops,
            UnasShopResource::class,
            $request->only(['search', 'sort', 'direction'])
        ));
    }

    public function options(): JsonResponse
    {
        $warehouses = Warehouse::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'warehouses' => $warehouses,
        ]);
    }

    public function store(StoreUnasShopRequest $request): JsonResponse
    {
        $shop = UnasShop::query()->create($request->validated());

        return response()->json([
            'data' => new UnasShopResource($shop),
        ], 201);
    }

    public function show(UnasShop $unasShop): JsonResponse
    {
        $unasShop->load(['warehouse']);

        return response()->json([
            'data' => new UnasShopResource($unasShop),
        ]);
    }

    public function update(UpdateUnasShopRequest $request, UnasShop $unasShop): JsonResponse
    {
        $unasShop->update($request->validated());

        return response()->json([
            'data' => new UnasShopResource($unasShop),
        ]);
    }

    public function destroy(UnasShop $unasShop): JsonResponse
    {
        $unasShop->delete();

        return response()->json(null, 204);
    }
}

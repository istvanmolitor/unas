<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Molitor\Admin\Http\Resources\DataTableResource;
use Molitor\Unas\Http\Resources\UnasOrderResource;
use Molitor\Unas\Models\UnasOrder;

class UnasOrderController
{
    public function index(Request $request): JsonResponse
    {
        $query = UnasOrder::query()->with(['shop', 'order']);

        if ($shopId = $request->input('unas_shop_id')) {
            $query->where('unas_shop_id', $shopId);
        }

        if ($search = $request->string('search')->toString()) {
            $query->whereHas('order', function ($q) use ($search): void {
                $q->where('code', 'like', '%'.$search.'%');
            });
        }

        $allowedSortFields = ['id', 'remote_id', 'changed', 'created_at'];
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'desc') === 'desc' ? 'desc' : 'asc';

        if (in_array($sort, $allowedSortFields, true)) {
            $query->orderBy($sort, $direction);
        }

        $orders = $query
            ->paginate((int) $request->input('per_page', 10))
            ->withQueryString();

        return response()->json(new DataTableResource(
            $orders,
            UnasOrderResource::class,
            $request->only(['search', 'sort', 'direction', 'unas_shop_id'])
        ));
    }

    public function show(UnasOrder $unasOrder): JsonResponse
    {
        $unasOrder->load(['shop', 'order']);

        return response()->json([
            'data' => new UnasOrderResource($unasOrder),
        ]);
    }

    public function destroy(UnasOrder $unasOrder): JsonResponse
    {
        $unasOrder->delete();

        return response()->json(null, 204);
    }
}


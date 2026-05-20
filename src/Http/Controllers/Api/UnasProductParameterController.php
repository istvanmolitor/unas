<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Molitor\Admin\Http\Resources\DataTableResource;
use Molitor\Unas\Http\Resources\UnasProductParameterResource;
use Molitor\Unas\Models\UnasProductParameter;

class UnasProductParameterController
{
    public function index(Request $request): JsonResponse
    {
        $query = UnasProductParameter::query()->with(['shop', 'language']);

        if ($shopId = $request->input('unas_shop_id')) {
            $query->where('unas_shop_id', $shopId);
        }

        if ($search = $request->string('search')->toString()) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $allowedSortFields = ['id', 'name', 'type', 'order', 'created_at'];
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        if (in_array($sort, $allowedSortFields, true)) {
            $query->orderBy($sort, $direction);
        }

        $parameters = $query
            ->paginate((int) $request->input('per_page', 10))
            ->withQueryString();

        return response()->json(new DataTableResource(
            $parameters,
            UnasProductParameterResource::class,
            $request->only(['search', 'sort', 'direction', 'unas_shop_id'])
        ));
    }

    public function show(UnasProductParameter $unasProductParameter): JsonResponse
    {
        $unasProductParameter->load(['shop', 'language']);

        return response()->json([
            'data' => new UnasProductParameterResource($unasProductParameter),
        ]);
    }

    public function destroy(UnasProductParameter $unasProductParameter): JsonResponse
    {
        $unasProductParameter->delete();

        return response()->json(null, 204);
    }
}

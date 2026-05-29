<?php

declare(strict_types=1);

namespace Molitor\Unas\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Molitor\Unas\Http\Requests\StoreUnasProductImageRequest;
use Molitor\Unas\Http\Requests\UpdateUnasProductImageRequest;
use Molitor\Unas\Http\Resources\UnasProductImageResource;
use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Models\UnasProductImage;
use Molitor\Unas\Repositories\UnasProductImageRepositoryInterface;

class UnasProductImageController
{
    public function __construct(
        private UnasProductImageRepositoryInterface $unasProductImageRepository
    ) {}

    public function index(UnasProduct $unasProduct): JsonResponse
    {
        $images = $unasProduct->images()->get();

        return response()->json([
            'data' => UnasProductImageResource::collection($images),
        ]);
    }

    public function store(StoreUnasProductImageRequest $request, UnasProduct $unasProduct): JsonResponse
    {
        $validated = $request->validated();

        $image = $this->unasProductImageRepository->create(
            $unasProduct,
            $validated['image_url'],
            (bool) ($validated['is_main'] ?? false),
            $validated['sort'] ?? null,
        );

        return response()->json([
            'data' => new UnasProductImageResource($image),
        ], 201);
    }

    public function update(UpdateUnasProductImageRequest $request, UnasProduct $unasProduct, UnasProductImage $image): JsonResponse
    {
        $validated = $request->validated();

        if (! empty($validated['is_main']) && $validated['is_main']) {
            $unasProduct->images()->where('id', '!=', $image->id)->update(['is_main' => false]);
        }

        $image->update($validated);

        return response()->json([
            'data' => new UnasProductImageResource($image),
        ]);
    }

    public function destroy(UnasProduct $unasProduct, UnasProductImage $image): JsonResponse
    {
        $image->delete();

        return response()->json(null, 204);
    }
}


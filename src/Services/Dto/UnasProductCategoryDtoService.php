<?php

namespace Molitor\Unas\Services\Dto;

use Molitor\Product\Dto\ImageDto;
use Molitor\Product\Dto\ProductCategoryDto;
use Molitor\Unas\Models\UnasProductCategory;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Repositories\UnasProductCategoryRepositoryInterface;

class UnasProductCategoryDtoService
{
    public function __construct(
        protected UnasProductCategoryRepositoryInterface $unasProductCategoryRepository,
    )
    {
    }

    /**
     * Create DTO from UnasProductCategory model
     */
    public function makeDto(UnasProductCategory $category): ProductCategoryDto
    {
        $dto = new ProductCategoryDto();
        $dto->id = $category->id;
        $dto->source = 'unas';

        // Build path from category hierarchy
        $pathCategories = $this->unasProductCategoryRepository->getPathCategories($category);
        foreach ($pathCategories as $pathCategory) {
            $dto->path->addItem()->set('hu', (string)$pathCategory);
        }

        // Set description
        if ($category->description) {
            $dto->description->set('hu', $category->description);
        }

        // Set image if available
        if ($category->image_url) {
            $imageDto = new ImageDto();
            $imageDto->url = $category->image_url;
            $dto->image = $imageDto;
        }

        return $dto;
    }

    /**
     * Save DTO to database as UnasProductCategory
     */
    public function saveDto(UnasShop $shop, ProductCategoryDto $dto): ?UnasProductCategory
    {
        // Get or create the category by path
        $path = $dto->path->getArrayPath('hu');
        $category = $this->getOrCreateByPath($shop, $path);

        if (!$category) {
            return null;
        }

        // Fill the category with DTO data
        $this->fillModel($category, $dto);
        $category->save();

        return $category;
    }

    /**
     * Get or create UnasProductCategory model from DTO
     */
    public function makeModel(UnasShop $shop, ProductCategoryDto $dto): ?UnasProductCategory
    {
        $path = $dto->path->getArrayPath('hu');
        return $this->getOrCreateByPath($shop, $path);
    }

    /**
     * Fill UnasProductCategory model from DTO
     */
    public function fillModel(UnasProductCategory $category, ProductCategoryDto $dto): void
    {
        if ($dto->description->has('hu')) {
            $category->description = $dto->description->get('hu');
        }

        if ($dto->image) {
            $category->image_url = $dto->image->url;
        }

        if ($dto->id && $dto->source === 'unas_api') {
            $category->remote_id = $dto->id;
        }

        $category->changed = false;
    }

    /**
     * Get or create category by path
     */
    protected function getOrCreateByPath(UnasShop $shop, array $path): ?UnasProductCategory
    {
        $count = count($path);
        if ($count == 0) {
            return null;
        }

        $parent = null;
        foreach ($path as $name) {
            if ($parent === null) {
                $existing = $this->unasProductCategoryRepository->getRootCategoryByName($shop, $name);
                if ($existing) {
                    $parent = $existing;
                } else {
                    $parent = $this->unasProductCategoryRepository->createRootCategory($shop, (string)$name);
                }
            } else {
                $existing = $this->unasProductCategoryRepository->getSubCategoryByName($parent, $name);
                if ($existing) {
                    $parent = $existing;
                } else {
                    $parent = $this->unasProductCategoryRepository->createSubCategory($parent, (string)$name);
                }
            }

            if (!$parent) {
                return null;
            }
        }

        return $parent;
    }
}


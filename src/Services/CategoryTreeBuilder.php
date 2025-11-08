<?php

namespace Molitor\Unas\Services;

class CategoryTreeBuilder
{
    private array $rootIds = [];
    private array $tree = [];
    private array $items = [];

    public function idExists(int $id): bool
    {
        return array_key_exists($id, $this->items);
    }

    public function add(int $id, int $parentId, array $data): void
    {
        if(!$this->idExists($id)) {
            if($parentId === 0) {
                $this->rootIds[] = $id;
            }
            else {
                if(array_key_exists($parentId, $this->tree)) {
                    $this->tree[$parentId][] = $id;
                }
                else {
                    $this->tree[$parentId] = [$id];
                }
            }

            $this->items[$id] = $data;
        }
    }

    public function getChildrenIds(int $id): array
    {
        if($id === 0) {
            return $this->rootIds;
        }
        if(array_key_exists($id, $this->tree)) {
            return $this->tree[$id];
        }
        return [];
    }

    public function getItem(int $id): array
    {
        return $this->items[$id];
    }

    public function getTree()
    {
        return $this->tree;
    }
}
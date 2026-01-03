<?php

namespace Molitor\Unas\Livewire;

use Livewire\Component;
use Molitor\Unas\Models\UnasProductCategory;

class UnasCategoryTreeItem extends Component
{
    public UnasProductCategory $category;
    public bool $isOpen = false;
    public int $level = 0;

    public function mount(UnasProductCategory $category, int $level = 0): void
    {
        $this->category = $category;
        $this->level = $level;
    }

    public function toggle(): void
    {
        $this->isOpen = !$this->isOpen;
    }

    public function render()
    {
        $children = $this->category
            ->childCategories()
            ->orderBy('name')
            ->get();

        return view('unas::livewire.unas-category-tree-item', [
            'children' => $children,
        ]);
    }
}


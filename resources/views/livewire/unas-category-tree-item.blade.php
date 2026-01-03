<div class="unas-category-tree-item">
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between p-4">
            <div class="flex items-center space-x-3 flex-1">
                @if($children->isNotEmpty())
                    <button
                        wire:click="toggle"
                        class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                        type="button"
                    >
                        @if($isOpen)
                            <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-500" />
                        @else
                            <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-500" />
                        @endif
                    </button>
                    <x-heroicon-o-folder class="w-5 h-5 text-amber-500" />
                @else
                    <div class="w-6"></div>
                    <x-heroicon-o-tag class="w-5 h-5 text-gray-400" />
                @endif

                <div class="flex-1">
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ $category->name }}
                    </span>
                    @if($category->title)
                        <span class="text-xs text-gray-500 ml-2">
                            ({{ $category->title }})
                        </span>
                    @endif
                    @if($category->remote_id)
                        <span class="text-xs text-blue-500 ml-2">
                            ID: {{ $category->remote_id }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <div class="flex items-center space-x-1 text-xs text-gray-500">
                    @if($category->display_page)
                        <span class="px-2 py-1 bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300 rounded">
                            Oldalon
                        </span>
                    @endif
                    @if($category->display_menu)
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 rounded">
                            Menüben
                        </span>
                    @endif
                </div>

                <a
                    href="{{ \Molitor\Unas\Filament\Resources\UnasProductCategoryResource::getUrl('edit', ['record' => $category->id]) }}"
                    class="p-2 text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    title="Szerkesztés"
                >
                    <x-heroicon-o-pencil class="w-4 h-4" />
                </a>
            </div>
        </div>

        @if($isOpen && $children->isNotEmpty())
            <div class="px-4 pb-4 ml-6 space-y-2">
                @foreach($children as $child)
                    <livewire:unas-category-tree-item
                        :category="$child"
                        :level="$level + 1"
                        :key="'unas-category-' . $child->id"
                        :wire:key="'unas-category-' . $child->id"
                    />
                @endforeach
            </div>
        @endif
    </div>
</div>


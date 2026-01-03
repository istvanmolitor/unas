<?php

namespace Molitor\Unas\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Gate;
use Molitor\Unas\Filament\Resources\UnasProductCategoryResource;
use Molitor\Unas\Repositories\UnasProductCategoryRepositoryInterface;
use Molitor\Unas\Models\UnasShop;

class UnasProductCategoriesPage extends Page
{
    protected string $view = 'unas::filament.pages.unas-product-categories';

    protected static ?string $slug = 'unas-product-categories-page';

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static bool $shouldRegisterNavigation = false;

    public $categories = [];
    public ?int $shopId = null;

    public static function canAccess(): bool
    {
        return Gate::allows('acl', 'unas');
    }

    public static function getNavigationGroup(): string
    {
        return __('unas::common.unas');
    }

    public static function getNavigationLabel(): string
    {
        return __('unas::common.unas_categories');
    }

    public function getTitle(): string|Htmlable
    {
        $shop = $this->getShop();
        if ($shop) {
            return __('unas::common.categories_shop', ['shop' => $shop->name]);
        }
        return __('unas::common.unas_categories');
    }

    public function mount(): void
    {
        $this->shopId = request()->integer('shop_id');
        $this->loadCategories();
    }

    protected function getShop(): ?UnasShop
    {
        if (!$this->shopId) {
            return null;
        }
        return UnasShop::find($this->shopId);
    }

    protected function loadCategories(): void
    {
        if (!$this->shopId) {
            $this->categories = collect();
            return;
        }

        $shop = $this->getShop();
        if (!$shop) {
            $this->categories = collect();
            return;
        }

        /** @var UnasProductCategoryRepositoryInterface $categoryRepository */
        $categoryRepository = app(UnasProductCategoryRepositoryInterface::class);
        $categories = $categoryRepository->getRootCategories($shop);
        $this->categories = $categories;
    }

    protected function getHeaderActions(): array
    {
        $url = UnasProductCategoryResource::getUrl('index');
        if ($this->shopId) {
            $url .= '?shop_id=' . $this->shopId;
        }

        return [
            Action::make('list_view')
                ->label('Lista nézet')
                ->icon('heroicon-o-list-bullet')
                ->url($url)
                ->color('gray'),
        ];
    }
}


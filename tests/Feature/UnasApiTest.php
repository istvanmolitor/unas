<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Molitor\Language\Models\Language;
use Molitor\Product\Models\ProductUnit;
use Molitor\Stock\Models\Warehouse;
use Molitor\Unas\Models\UnasProduct;
use Molitor\Unas\Models\UnasProductImage;
use Molitor\Unas\Models\UnasProductCategory;
use Molitor\Unas\Models\UnasProductParameter;
use Molitor\Unas\Models\UnasShop;
use Tests\TestCase;

class UnasApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_unas_shops(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $warehouse = Warehouse::query()->create([
            'is_primary' => true,
            'name' => 'Kozponti raktar',
        ]);

        UnasShop::query()->create([
            'enabled' => true,
            'domain' => 'test-shop.hu',
            'name' => 'Test Shop',
            'api_key' => 'secret-key',
            'warehouse_id' => $warehouse->id,
        ]);

        $response = $this->getJson('/api/unas/shops');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Test Shop');
    }

    public function test_can_create_unas_shop(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $warehouse = Warehouse::query()->create([
            'is_primary' => true,
            'name' => 'Kozponti raktar',
        ]);

        $response = $this->postJson('/api/unas/shops', [
            'enabled' => true,
            'domain' => 'new-shop.hu',
            'name' => 'New Shop',
            'api_key' => 'new-key',
            'warehouse_id' => $warehouse->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'New Shop');

        $this->assertDatabaseHas('unas_shops', [
            'name' => 'New Shop',
            'domain' => 'new-shop.hu',
        ]);
    }

    public function test_warehouse_id_is_required_when_creating_unas_shop(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/unas/shops', [
            'enabled' => true,
            'domain' => 'new-shop.hu',
            'name' => 'New Shop',
            'api_key' => 'new-key',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['warehouse_id']);
    }

    public function test_can_update_unas_product(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $warehouse = Warehouse::query()->create([
            'is_primary' => true,
            'name' => 'Kozponti raktar',
        ]);

        $shop = UnasShop::query()->create([
            'enabled' => true,
            'domain' => 'test-shop.hu',
            'name' => 'Test Shop',
            'api_key' => 'secret-key',
            'warehouse_id' => $warehouse->id,
        ]);

        $productUnit = ProductUnit::query()->create([
            'enabled' => true,
            'code' => 'db',
        ]);

        $product = UnasProduct::query()->create([
            'sku' => 'SKU-1',
            'unas_shop_id' => $shop->id,
            'product_unit_id' => $productUnit->id,
            'price' => 1000,
            'stock' => 5,
            'changed' => false,
        ]);

        $response = $this->putJson('/api/unas/products/'.$product->id, [
            'price' => 1499,
            'stock' => 12,
            'changed' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.price', 1499)
            ->assertJsonPath('data.stock', 12)
            ->assertJsonPath('data.changed', true);

        $this->assertDatabaseHas('unas_products', [
            'id' => $product->id,
            'price' => 1499,
            'stock' => 12,
            'changed' => true,
        ]);
    }

    public function test_can_show_unas_product(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $warehouse = Warehouse::query()->create([
            'is_primary' => true,
            'name' => 'Kozponti raktar',
        ]);

        $shop = UnasShop::query()->create([
            'enabled' => true,
            'domain' => 'test-shop.hu',
            'name' => 'Test Shop',
            'api_key' => 'secret-key',
            'warehouse_id' => $warehouse->id,
        ]);

        $productUnit = ProductUnit::query()->create([
            'enabled' => true,
            'code' => 'db',
        ]);

        $product = UnasProduct::query()->create([
            'sku' => 'SKU-2',
            'unas_shop_id' => $shop->id,
            'product_unit_id' => $productUnit->id,
            'price' => 1999,
            'stock' => 7,
            'changed' => false,
        ]);

        $response = $this->getJson('/api/unas/products/'.$product->id);

        $response->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.sku', 'SKU-2')
            ->assertJsonPath('data.unas_shop_id', $shop->id);
    }

    public function test_products_index_returns_first_image_when_main_image_is_not_marked(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $warehouse = Warehouse::query()->create([
            'is_primary' => true,
            'name' => 'Kozponti raktar',
        ]);

        $shop = UnasShop::query()->create([
            'enabled' => true,
            'domain' => 'test-shop.hu',
            'name' => 'Test Shop',
            'api_key' => 'secret-key',
            'warehouse_id' => $warehouse->id,
        ]);

        $productUnit = ProductUnit::query()->create([
            'enabled' => true,
            'code' => 'db',
        ]);

        $product = UnasProduct::query()->create([
            'sku' => 'SKU-IMAGE-1',
            'unas_shop_id' => $shop->id,
            'product_unit_id' => $productUnit->id,
            'price' => 1000,
            'stock' => 10,
            'changed' => false,
        ]);

        UnasProductImage::query()->create([
            'unas_product_id' => $product->id,
            'image_url' => 'https://example.com/image-1.jpg',
            'is_main' => false,
            'sort' => 0,
        ]);

        UnasProductImage::query()->create([
            'unas_product_id' => $product->id,
            'image_url' => 'https://example.com/image-2.jpg',
            'is_main' => false,
            'sort' => 1,
        ]);

        $response = $this->getJson('/api/unas/products?unas_shop_id='.$shop->id);

        $response->assertOk()
            ->assertJsonPath('data.0.id', $product->id)
            ->assertJsonPath('data.0.main_image_url', 'https://example.com/image-1.jpg');
    }

    public function test_can_show_unas_shop_with_related_counts(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $warehouse = Warehouse::query()->create([
            'is_primary' => true,
            'name' => 'Kozponti raktar',
        ]);

        $shop = UnasShop::query()->create([
            'enabled' => true,
            'domain' => 'test-shop.hu',
            'name' => 'Test Shop',
            'api_key' => 'secret-key',
            'warehouse_id' => $warehouse->id,
        ]);

        $productUnit = ProductUnit::query()->create([
            'enabled' => true,
            'code' => 'db',
        ]);

        UnasProduct::query()->create([
            'sku' => 'SKU-COUNT-1',
            'unas_shop_id' => $shop->id,
            'product_unit_id' => $productUnit->id,
            'price' => 1000,
            'stock' => 5,
            'changed' => false,
        ]);

        UnasProductCategory::query()->create([
            'unas_shop_id' => $shop->id,
            'parent_id' => 0,
            'name' => 'Kategoria 1',
            'title' => 'Kategoria 1',
            'display_page' => true,
            'display_menu' => true,
            'changed' => false,
        ]);

        $language = Language::query()->create([
            'enabled' => true,
            'code' => 'hu',
        ]);

        UnasProductParameter::query()->create([
            'unas_shop_id' => $shop->id,
            'name' => 'Szelesseg',
            'type' => 'select',
            'language_id' => $language->id,
            'order' => 1,
            'changed' => false,
        ]);

        $response = $this->getJson('/api/unas/shops/'.$shop->id);

        $response->assertOk()
            ->assertJsonPath('data.id', $shop->id)
            ->assertJsonPath('data.shop_products_count', 1)
            ->assertJsonPath('data.shop_product_categories_count', 1)
            ->assertJsonPath('data.shop_product_parameters_count', 1);
    }

    public function test_cannot_update_unas_product_shop_or_remote_id(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $warehouse = Warehouse::query()->create([
            'is_primary' => true,
            'name' => 'Kozponti raktar',
        ]);

        $shop = UnasShop::query()->create([
            'enabled' => true,
            'domain' => 'test-shop.hu',
            'name' => 'Test Shop',
            'api_key' => 'secret-key',
            'warehouse_id' => $warehouse->id,
        ]);

        $otherShop = UnasShop::query()->create([
            'enabled' => true,
            'domain' => 'other-shop.hu',
            'name' => 'Other Shop',
            'api_key' => 'other-secret-key',
            'warehouse_id' => $warehouse->id,
        ]);

        $productUnit = ProductUnit::query()->create([
            'enabled' => true,
            'code' => 'db',
        ]);

        $product = UnasProduct::query()->create([
            'sku' => 'SKU-3',
            'unas_shop_id' => $shop->id,
            'product_unit_id' => $productUnit->id,
            'price' => 2199,
            'stock' => 4,
            'remote_id' => 111,
            'changed' => false,
        ]);

        $response = $this->putJson('/api/unas/products/'.$product->id, [
            'unas_shop_id' => $otherShop->id,
            'remote_id' => 222,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['unas_shop_id', 'remote_id']);

        $this->assertDatabaseHas('unas_products', [
            'id' => $product->id,
            'unas_shop_id' => $shop->id,
            'remote_id' => 111,
        ]);
    }
}


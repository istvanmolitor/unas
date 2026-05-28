<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Molitor\Product\Models\ProductUnit;
use Molitor\Stock\Models\Warehouse;
use Molitor\Unas\Models\UnasProduct;
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
}


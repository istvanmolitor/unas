<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Molitor\Stock\Models\Warehouse;
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
}


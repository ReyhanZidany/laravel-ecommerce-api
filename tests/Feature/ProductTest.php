<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private function adminToken(): string
    {
        $admin = User::factory()->create(['role' => 'admin']);

        return $admin->createToken('test')->plainTextToken;
    }

    private function userToken(): string
    {
        $user = User::factory()->create(['role' => 'user']);

        return $user->createToken('test')->plainTextToken;
    }

    public function test_anyone_can_list_products(): void
    {
        Product::factory()->count(3)->create();

        $this->getJson('/api/products')->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_anyone_can_view_single_product(): void
    {
        $product = Product::factory()->create();

        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonFragment(['name' => $product->name]);
    }

    public function test_admin_can_create_product(): void
    {
        $this->withToken($this->adminToken())
            ->postJson('/api/products', [
                'name'   => 'Test Product',
                'price'  => 99000,
                'status' => 'active',
            ])
            ->assertCreated()
            ->assertJsonFragment(['name' => 'Test Product']);
    }

    public function test_non_admin_cannot_create_product(): void
    {
        $this->withToken($this->userToken())
            ->postJson('/api/products', [
                'name'   => 'Test Product',
                'price'  => 99000,
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::factory()->create();

        $this->withToken($this->adminToken())
            ->putJson("/api/products/{$product->id}", ['name' => 'Updated Name'])
            ->assertOk()
            ->assertJsonFragment(['name' => 'Updated Name']);
    }

    public function test_admin_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $this->withToken($this->adminToken())
            ->deleteJson("/api/products/{$product->id}")
            ->assertOk();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}

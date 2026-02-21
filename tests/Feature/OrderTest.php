<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private function adminToken(): string
    {
        $admin = User::factory()->create(['role' => 'admin']);

        return $admin->createToken('test')->plainTextToken;
    }

    private function activeProduct(array $attrs = []): Product
    {
        return Product::factory()->create(array_merge(['status' => 'active', 'price' => 100000], $attrs));
    }

    public function test_anyone_can_create_order(): void
    {
        $product = $this->activeProduct();

        $this->postJson('/api/orders', [
            'customer_name'  => 'Budi',
            'customer_email' => 'budi@mail.com',
            'items'          => [['product_id' => $product->id, 'qty' => 2]],
        ])->assertCreated()->assertJsonFragment(['customer_name' => 'Budi']);
    }

    public function test_order_total_is_calculated_correctly(): void
    {
        $product = $this->activeProduct(['price' => 50000]);

        $response = $this->postJson('/api/orders', [
            'customer_name'  => 'Ani',
            'customer_email' => 'ani@mail.com',
            'items'          => [['product_id' => $product->id, 'qty' => 3]],
        ])->assertCreated();

        $response->assertJsonFragment(['total_price' => 150000.00]);
    }

    public function test_cannot_order_inactive_product(): void
    {
        $product = Product::factory()->create(['status' => 'inactive']);

        $this->postJson('/api/orders', [
            'customer_name'  => 'Cici',
            'customer_email' => 'cici@mail.com',
            'items'          => [['product_id' => $product->id, 'qty' => 1]],
        ])->assertUnprocessable();
    }

    public function test_admin_can_list_orders(): void
    {
        $this->withToken($this->adminToken())
            ->getJson('/api/orders')
            ->assertOk();
    }

    public function test_admin_can_view_single_order(): void
    {
        $product = $this->activeProduct();

        $orderResponse = $this->postJson('/api/orders', [
            'customer_name'  => 'Dodi',
            'customer_email' => 'dodi@mail.com',
            'items'          => [['product_id' => $product->id, 'qty' => 1]],
        ]);

        $orderId = $orderResponse->json('data.id');

        $this->withToken($this->adminToken())
            ->getJson("/api/orders/{$orderId}")
            ->assertOk()
            ->assertJsonFragment(['customer_name' => 'Dodi']);
    }

    public function test_non_admin_cannot_list_orders(): void
    {
        $user  = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/orders')->assertForbidden();
    }
}

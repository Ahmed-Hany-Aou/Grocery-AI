<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::create([
            'name' => 'Test Category',
            'description' => 'Test Description'
        ]);
    }

    /** @test */
    public function test_can_list_products(): void
    {
        Product::create([
            'name' => 'Test Product',
            'category_id' => $this->category->id,
            'price' => 10.00,
        ]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'price', 'category']
                ],
                'meta' => ['current_page', 'total']
            ]);
    }

    /** @test */
    public function test_can_create_product(): void
    {
        $productData = [
            'name' => 'New Product',
            'category_id' => $this->category->id,
            'price' => 25.50,
            'barcode' => '1234567890123',
            'stock_quantity' => 100,
            'unit' => 'kg',
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully'
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'price' => 25.50
        ]);
    }

    /** @test */
    public function test_can_show_product(): void
    {
        $product = Product::create([
            'name' => 'Show Product',
            'category_id' => $this->category->id,
            'price' => 15.00,
        ]);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'name' => 'Show Product'
                ]
            ]);
    }

    /** @test */
    public function test_can_update_product(): void
    {
        $product = Product::create([
            'name' => 'Old Name',
            'category_id' => $this->category->id,
            'price' => 10.00,
        ]);

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Updated Name',
            'price' => 20.00
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price' => 20.00
        ]);
    }

    /** @test */
    public function test_can_delete_product(): void
    {
        $product = Product::create([
            'name' => 'Delete Me',
            'category_id' => $this->category->id,
            'price' => 5.00,
        ]);

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function test_can_search_products(): void
    {
        Product::create([
            'name' => 'Apple',
            'category_id' => $this->category->id,
            'price' => 5.00,
        ]);
        Product::create([
            'name' => 'Banana',
            'category_id' => $this->category->id,
            'price' => 3.00,
        ]);

        $response = $this->getJson('/api/v1/products?search=apple');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Apple', $data[0]['name']);
    }

    /** @test */
    public function test_can_filter_products_by_category(): void
    {
        $category2 = Category::create(['name' => 'Category 2']);
        
        Product::create([
            'name' => 'Product 1',
            'category_id' => $this->category->id,
            'price' => 5.00,
        ]);
        Product::create([
            'name' => 'Product 2',
            'category_id' => $category2->id,
            'price' => 3.00,
        ]);

        $response = $this->getJson("/api/v1/products?category_id={$this->category->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    /** @test */
    public function test_product_validation_requires_name(): void
    {
        $response = $this->postJson('/api/v1/products', [
            'category_id' => $this->category->id,
            'price' => 10.00,
            // missing 'name'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function test_can_find_product_by_barcode(): void
    {
        $product = Product::create([
            'name' => 'Barcode Product',
            'category_id' => $this->category->id,
            'price' => 15.00,
            'barcode' => '9876543210123',
        ]);

        $response = $this->getJson('/api/v1/products/barcode/9876543210123');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'barcode' => '9876543210123'
                ]
            ]);
    }
}

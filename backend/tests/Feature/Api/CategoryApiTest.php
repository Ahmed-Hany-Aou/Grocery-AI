<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_can_list_categories(): void
    {
        Category::create(['name' => 'Fruits']);
        Category::create(['name' => 'Vegetables']);

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name']
                ]
            ]);
        
        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function test_can_create_category(): void
    {
        $response = $this->postJson('/api/v1/categories', [
            'name' => 'Dairy',
            'description' => 'Milk products'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Category created successfully'
            ]);

        $this->assertDatabaseHas('categories', ['name' => 'Dairy']);
    }

    /** @test */
    public function test_can_show_category(): void
    {
        $category = Category::create(['name' => 'Bakery']);

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['name' => 'Bakery']
            ]);
    }

    /** @test */
    public function test_can_update_category(): void
    {
        $category = Category::create(['name' => 'Old Name']);

        $response = $this->putJson("/api/v1/categories/{$category->id}", [
            'name' => 'New Name'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('categories', ['name' => 'New Name']);
    }

    /** @test */
    public function test_can_delete_empty_category(): void
    {
        $category = Category::create(['name' => 'Empty Category']);

        $response = $this->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /** @test */
    public function test_cannot_delete_category_with_products(): void
    {
        $category = Category::create(['name' => 'Has Products']);
        Product::create([
            'name' => 'Product',
            'category_id' => $category->id,
            'price' => 10.00
        ]);

        $response = $this->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot delete category with existing products'
            ]);
    }

    /** @test */
    public function test_categories_include_product_count(): void
    {
        $category = Category::create(['name' => 'Counted']);
        Product::create(['name' => 'P1', 'category_id' => $category->id, 'price' => 5]);
        Product::create(['name' => 'P2', 'category_id' => $category->id, 'price' => 5]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(2, $data[0]['products_count']);
    }

    /** @test */
    public function test_category_name_must_be_unique(): void
    {
        Category::create(['name' => 'Unique']);

        $response = $this->postJson('/api/v1/categories', [
            'name' => 'Unique'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}

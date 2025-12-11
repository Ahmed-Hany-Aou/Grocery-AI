<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;
    protected Product $product1;
    protected Product $product2;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->category = Category::create(['name' => 'Test Category']);
        $this->product1 = Product::create([
            'name' => 'Product 1',
            'category_id' => $this->category->id,
            'price' => 10.00,
        ]);
        $this->product2 = Product::create([
            'name' => 'Product 2',
            'category_id' => $this->category->id,
            'price' => 20.00,
        ]);
    }

    /** @test */
    public function test_can_list_invoices(): void
    {
        Invoice::create([
            'invoice_number' => 'INV-001',
            'type' => 'sale',
            'total_amount' => 100.00
        ]);

        $response = $this->getJson('/api/v1/invoices');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'invoice_number', 'total_amount']
                ]
            ]);
    }

    /** @test */
    public function test_can_create_invoice_with_items(): void
    {
        $response = $this->postJson('/api/v1/invoices', [
            'type' => 'sale',
            'items' => [
                ['product_id' => $this->product1->id, 'quantity' => 2],
                ['product_id' => $this->product2->id, 'quantity' => 3],
            ]
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        // Check invoice created
        $this->assertDatabaseHas('invoices', ['type' => 'sale']);
        
        // Check items created
        $invoice = Invoice::first();
        $this->assertCount(2, $invoice->items);
    }

    /** @test */
    public function test_invoice_total_calculated_correctly(): void
    {
        $response = $this->postJson('/api/v1/invoices', [
            'items' => [
                ['product_id' => $this->product1->id, 'quantity' => 2], // 2 * 10 = 20
                ['product_id' => $this->product2->id, 'quantity' => 3], // 3 * 20 = 60
            ]
        ]);

        $response->assertStatus(201);
        
        $invoice = Invoice::first();
        $this->assertEquals(80.00, $invoice->total_amount); // 20 + 60 = 80
    }

    /** @test */
    public function test_can_show_invoice_with_items(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-SHOW',
            'type' => 'sale',
            'total_amount' => 30.00
        ]);
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $this->product1->id,
            'product_name' => $this->product1->name,
            'quantity' => 3,
            'unit_price' => 10.00,
            'total_price' => 30.00,
        ]);

        $response = $this->getJson("/api/v1/invoices/{$invoice->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id', 'invoice_number', 'items' => [
                        '*' => ['product_name', 'quantity', 'total_price']
                    ]
                ]
            ]);
    }

    /** @test */
    public function test_can_add_item_to_invoice(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-ADD',
            'type' => 'sale',
            'total_amount' => 0
        ]);

        $response = $this->postJson("/api/v1/invoices/{$invoice->id}/items", [
            'product_id' => $this->product1->id,
            'quantity' => 5
        ]);

        $response->assertStatus(200);
        
        $invoice->refresh();
        $this->assertCount(1, $invoice->items);
        $this->assertEquals(50.00, $invoice->total_amount); // 5 * 10
    }

    /** @test */
    public function test_can_remove_item_from_invoice(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-REMOVE',
            'type' => 'sale',
            'total_amount' => 30.00
        ]);
        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $this->product1->id,
            'product_name' => $this->product1->name,
            'quantity' => 3,
            'unit_price' => 10.00,
            'total_price' => 30.00,
        ]);

        $response = $this->deleteJson("/api/v1/invoices/{$invoice->id}/items/{$item->id}");

        $response->assertStatus(200);
        
        $invoice->refresh();
        $this->assertCount(0, $invoice->items);
        $this->assertEquals(0, $invoice->total_amount);
    }

    /** @test */
    public function test_invoice_requires_items(): void
    {
        $response = $this->postJson('/api/v1/invoices', [
            'type' => 'sale',
            // no items
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    /** @test */
    public function test_can_delete_invoice(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-DELETE',
            'type' => 'sale',
            'total_amount' => 0
        ]);

        $response = $this->deleteJson("/api/v1/invoices/{$invoice->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }
}

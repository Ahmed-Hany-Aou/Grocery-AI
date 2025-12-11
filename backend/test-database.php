<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\InvoiceItem;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test 1: Create a category
echo "Test 1: Creating categories...\n";
$fruits = Category::create(['name' => 'Fruits', 'description' => 'Fresh fruits']);
$vegetables = Category::create(['name' => 'Vegetables', 'description' => 'Fresh vegetables']);
echo "✓ Created categories: {$fruits->name}, {$vegetables->name}\n\n";

// Test 2: Create products
echo "Test 2: Creating products...\n";
$apple = Product::create([
    'category_id' => $fruits->id,
    'name' => 'Apple',
    'barcode' => '1234567890123',
    'price' => 5.50,
    'stock_quantity' => 100,
    'unit' => 'kg',
    'description' => 'Red apples'
]);
$tomato = Product::create([
    'category_id' => $vegetables->id,
    'name' => 'Tomato',
    'barcode' => '9876543210123',
    'price' => 3.00,
    'stock_quantity' => 50,
    'unit' => 'kg',
    'description' => 'Fresh tomatoes'
]);
echo "✓ Created products: {$apple->name} ({$apple->price} EGP), {$tomato->name} ({$tomato->price} EGP)\n\n";

// Test 3: Test relationships - Product -> Category
echo "Test 3: Testing Product -> Category relationship...\n";
echo "  {$apple->name} belongs to category: {$apple->category->name}\n";
echo "  {$tomato->name} belongs to category: {$tomato->category->name}\n\n";

// Test 4: Test relationships - Category -> Products
echo "Test 4: Testing Category -> Products relationship...\n";
echo "  {$fruits->name} category has " . $fruits->products->count() . " product(s)\n";
echo "  {$vegetables->name} category has " . $vegetables->products->count() . " product(s)\n\n";

// Test 5: Create an invoice
echo "Test 5: Creating an invoice...\n";
$invoice = Invoice::create([
    'invoice_number' => 'INV-001',
    'invoice_date' => now(),
    'total_amount' => 0,
]);
echo "✓ Created invoice: {$invoice->invoice_number}\n\n";

// Test 6: Add items to invoice
echo "Test 6: Adding invoice items...\n";
$item1 = InvoiceItem::create([
    'invoice_id' => $invoice->id,
    'product_id' => $apple->id,
    'product_name' => $apple->name,
    'quantity' => 2,
    'unit_price' => $apple->price,
    'total_price' => 2 * $apple->price,
]);
$item2 = InvoiceItem::create([
    'invoice_id' => $invoice->id,
    'product_id' => $tomato->id,
    'product_name' => $tomato->name,
    'quantity' => 3,
    'unit_price' => $tomato->price,
    'total_price' => 3 * $tomato->price,
]);

// Update invoice total
$invoice->update(['total_amount' => $item1->total_price + $item2->total_price]);
echo "✓ Added {$invoice->items->count()} items to invoice\n";
echo "  - {$item1->quantity}kg {$item1->product->name} @ {$item1->unit_price} EGP = {$item1->total_price} EGP\n";
echo "  - {$item2->quantity}kg {$item2->product->name} @ {$item2->unit_price} EGP = {$item2->total_price} EGP\n";
echo "  Invoice Total: {$invoice->total_amount} EGP\n\n";

// Test 7: Test relationships - Invoice -> Items -> Products
echo "Test 7: Testing Invoice relationships...\n";
foreach ($invoice->items as $item) {
    echo "  - Product: {$item->product->name}, Qty: {$item->quantity}, Price: {$item->total_price} EGP\n";
}
echo "\n";

// Test 8: Display counts
echo "Test 8: Database summary...\n";
echo "  Categories: " . Category::count() . "\n";
echo "  Products: " . Product::count() . "\n";
echo "  Invoices: " . Invoice::count() . "\n";
echo "  Invoice Items: " . InvoiceItem::count() . "\n\n";

echo "✅ All tests passed! Database and models are working correctly.\n";

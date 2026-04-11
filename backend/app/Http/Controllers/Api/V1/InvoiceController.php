<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use App\Services\AiService;

class InvoiceController extends Controller
{
    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }
    /**
     * Display a listing of invoices.
     * GET /api/v1/invoices
     */
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with('items.product');
        
        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        // Sort
        $query->orderBy('created_at', 'desc');
        
        // Paginate
        $perPage = $request->get('per_page', 15);
        $invoices = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => InvoiceResource::collection($invoices),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ]
        ]);
    }

    /**
     * Store a newly created invoice with items.
     * POST /api/v1/invoices
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_number' => 'nullable|string|max:50|unique:invoices',
            'type' => 'nullable|in:purchase,sale',
            'supplier_name' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);
        
        $invoice = DB::transaction(function () use ($validated) {
            // Generate invoice number if not provided
            $invoiceNumber = $validated['invoice_number'] ?? 'INV-' . now()->format('Ymd') . '-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT);
            
            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'type' => $validated['type'] ?? 'sale',
                'supplier_name' => $validated['supplier_name'] ?? null,
                'total_amount' => 0,
                'status' => 'completed',
            ]);
            
            $totalAmount = 0;
            
            // Add items
            foreach ($validated['items'] as $itemData) {
                $product = Product::find($itemData['product_id']);
                $quantity = $itemData['quantity'];
                $unitPrice = $product->price;
                $totalPrice = $quantity * $unitPrice;
                
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);
                
                $totalAmount += $totalPrice;
            }
            
            // Update invoice total
            $invoice->update(['total_amount' => $totalAmount]);
            
            return $invoice;
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Invoice created successfully',
            'data' => new InvoiceResource($invoice->load('items.product'))
        ], 201);
    }

    /**
     * Display the specified invoice.
     * GET /api/v1/invoices/{id}
     */
    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new InvoiceResource($invoice->load('items.product'))
        ]);
    }

    /**
     * Update the specified invoice.
     * PUT /api/v1/invoices/{id}
     */
    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'invoice_number' => 'nullable|string|max:50|unique:invoices,invoice_number,' . $invoice->id,
            'type' => 'nullable|in:purchase,sale',
            'supplier_name' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,processing,completed,failed',
        ]);
        
        $invoice->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully',
            'data' => new InvoiceResource($invoice->fresh()->load('items.product'))
        ]);
    }

    /**
     * Remove the specified invoice.
     * DELETE /api/v1/invoices/{id}
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        $invoice->delete(); // Items cascade delete
        
        return response()->json([
            'success' => true,
            'message' => 'Invoice deleted successfully'
        ]);
    }

    /**
     * Add an item to an existing invoice.
     * POST /api/v1/invoices/{invoice}/items
     */
    public function addItem(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
        ]);
        
        $product = Product::find($validated['product_id']);
        $quantity = $validated['quantity'];
        $unitPrice = $product->price;
        $totalPrice = $quantity * $unitPrice;
        
        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
        ]);
        
        // Recalculate invoice total
        $invoice->update([
            'total_amount' => $invoice->items()->sum('total_price')
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Item added to invoice',
            'data' => new InvoiceResource($invoice->fresh()->load('items.product'))
        ]);
    }

    /**
     * Remove an item from an invoice.
     * DELETE /api/v1/invoices/{invoice}/items/{item}
     */
    public function removeItem(Invoice $invoice, InvoiceItem $item): JsonResponse
    {
        if ($item->invoice_id !== $invoice->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item does not belong to this invoice'
            ], 422);
        }
        
        $item->delete();
        
        // Recalculate invoice total
        $invoice->update([
            'total_amount' => $invoice->items()->sum('total_price')
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Item removed from invoice',
            'data' => new InvoiceResource($invoice->fresh()->load('items.product'))
        ]);
    /**
     * Create a draft invoice from an AI-extracted image.
     * POST /api/v1/invoices/draft-from-ai
     */
    public function draftFromAi(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:5120',
            'command' => 'nullable|string'
        ]);

        $result = $this->aiService->extractFromImage($request->file('image'), $request->command);

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        $aiData = $result['data'];
        
        // We only proceed if it's a checkout type
        if (($aiData['type'] ?? 'checkout') !== 'checkout') {
            return response()->json([
                'success' => false,
                'message' => 'AI identified this as a stock-in request, not a sale.'
            ], 422);
        }

        $items = [];
        $totalAmount = 0;

        foreach ($aiData['products'] as $item) {
            $product = Product::where('name', 'ilike', '%' . $item['name'] . '%')
                ->orWhere('name_ar', 'ilike', '%' . ($item['name_ar'] ?? '') . '%')
                ->first();

            $unitPrice = $item['unit_price'] ?? ($product ? $product->price : 0);
            $quantity = $item['quantity'] ?? 1;
            
            $items[] = [
                'product_id' => $product ? $product->id : null,
                'product_name' => $product ? $product->name : $item['name'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $quantity * $unitPrice,
                'exists' => (bool)$product
            ];
            
            $totalAmount += ($quantity * $unitPrice);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'type' => 'sale',
                'items' => $items,
                'total_amount' => $totalAmount,
                'status' => 'pending'
            ]
        ]);
    }
}

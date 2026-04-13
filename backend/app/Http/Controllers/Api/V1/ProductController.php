<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Services\AiService;

class ProductController extends Controller
{
    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }
    /**
     * Display a listing of products.
     * GET /api/v1/products
     * 
     * Supports: ?category_id=1, ?search=tomato, ?per_page=15
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category');
        
        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Search by name or barcode
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }
        
        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);
        
        // Paginate
        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        ]);
    }

    /**
     * Store a newly created product.
     * POST /api/v1/products
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => new ProductResource($product->load('category'))
        ], 201);
    }

    /**
     * Display the specified product.
     * GET /api/v1/products/{id}
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new ProductResource($product->load('category'))
        ]);
    }

    /**
     * Update the specified product.
     * PUT /api/v1/products/{id}
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => new ProductResource($product->fresh()->load('category'))
        ]);
    }

    /**
     * Remove the specified product.
     * DELETE /api/v1/products/{id}
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    /**
     * Find product by barcode.
     * GET /api/v1/products/barcode/{barcode}
     */
    public function findByBarcode(string $barcode): JsonResponse
    {
        $product = Product::where('barcode', $barcode)->first();
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => new ProductResource($product->load('category'))
        ]);
    }
    /**
     * Bulk update stock quantity.
     * POST /api/v1/products/bulk-stock
     */
    public function bulkStockUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.id' => 'required|exists:products,id',
            'updates.*.quantity' => 'required|integer',
            'updates.*.mode' => 'required|in:add,set',
        ]);

        foreach ($request->updates as $update) {
            $product = Product::find($update['id']);
            if ($update['mode'] === 'add') {
                $product->increment('stock_quantity', $update['quantity']);
            } else {
                $product->update(['stock_quantity' => $update['quantity']]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully'
        ]);
    }

    /**
     * Process an image from Mohammed's camera to identify products or add stock.
     * POST /api/v1/products/process-image
     */
    public function processImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'command' => 'nullable|string'
        ]);

        $result = $this->aiService::extractFromImage($request->file('image'), $request->command);

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        $aiData = $result['data'];
        $foundProducts = [];

        foreach ($aiData['products'] as $item) {
            // Find existing product by name or barcode
            $product = Product::where('name', 'ilike', '%' . $item['name'] . '%')
                ->orWhere('name_ar', 'ilike', '%' . ($item['name_ar'] ?? '') . '%')
                ->first();

            if ($product) {
                $foundProducts[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'name_ar' => $product->name_ar,
                    'current_stock' => $product->stock_quantity,
                    'ai_quantity' => $item['quantity'],
                    'ai_unit_price' => $item['unit_price'],
                    'exists' => true
                ];
            } else {
                $foundProducts[] = [
                    'id' => null,
                    'name' => $item['name'],
                    'name_ar' => $item['name_ar'] ?? '',
                    'current_stock' => 0,
                    'ai_quantity' => $item['quantity'],
                    'ai_unit_price' => $item['unit_price'],
                    'exists' => false
                ];
            }
        }

        return response()->json([
            'success' => true,
            'type' => $aiData['type'],
            'products' => $foundProducts
        ]);
    }
}

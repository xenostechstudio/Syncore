<?php

namespace App\Http\Controllers\Api;

use App\Models\Inventory\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('low_stock')) {
            $threshold = config('inventory.low_stock_threshold', 10);
            $query->where('quantity', '<=', $threshold);
        }

        $products = $query->orderBy('name')->paginate($request->per_page ?? 15);

        return $this->paginated($products);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::with(['category', 'warehouse'])->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        return $this->success($product);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'status' => 'nullable|in:active,inactive',
        ]);

        // Set default values
        $validated['customer_lead_time'] = $validated['customer_lead_time'] ?? 1;

        $product = Product::create($validated);

        return $this->created($product, 'Product created successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'sku' => 'sometimes|string|max:50|unique:products,sku,' . $id,
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'cost_price' => 'sometimes|numeric|min:0',
            'selling_price' => 'sometimes|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'status' => 'nullable|in:active,inactive',
        ]);

        $product->update($validated);

        return $this->success($product, 'Product updated successfully');
    }

    public function stock(int $id): JsonResponse
    {
        $product = Product::with('warehouse')->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        return $this->success([
            'product_id' => $product->id,
            'total_quantity' => $product->quantity,
            'warehouse' => $product->warehouse ? [
                'id' => $product->warehouse->id,
                'name' => $product->warehouse->name,
            ] : null,
        ]);
    }
}

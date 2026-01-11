<?php

namespace App\Http\Controllers\Api;

use App\Models\Sales\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->orderBy('name')->paginate($request->per_page ?? 15);

        return $this->paginated($customers);
    }

    public function show(int $id): JsonResponse
    {
        $customer = Customer::with(['salesOrders' => fn($q) => $q->latest()->limit(5)])->find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        return $this->success($customer);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
        ]);

        $customer = Customer::create($validated);

        return $this->created($customer, 'Customer created successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
        ]);

        $customer->update($validated);

        return $this->success($customer, 'Customer updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        if ($customer->salesOrders()->exists()) {
            return $this->error('Cannot delete customer with existing orders', 422);
        }

        $customer->delete();

        return $this->success(null, 'Customer deleted successfully');
    }
}

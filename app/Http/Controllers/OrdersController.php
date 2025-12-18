<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrdersController extends Controller
{
    public function fetchUserOrders(Request $request)
    {
        try {
            // Default pagination parameters
            $perPage = $request->input('per_page', 10); // Default to 10 items per page 
            $page = $request->input('page', 1); // Default to page 1 
            $status = $request->input('status'); // Optional filter by status
            $userId = $request->input('user_id'); // Get user_id from request

            // Validate input
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'status' => 'nullable|string|in:pending,shipped,delivered',
            ]);

            // Fetch orders 
            $query = Orders::with(['shop', 'details.product', 'details.variant'])
                ->where('user_id', $userId);

            // Apply status filter if provided
            if ($status) {
                $query->where('order_status', $status);
            }

            // Paginate results
            $orders = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'status' => true,
                'message' => 'User orders fetched successfully.',
                'data' => $orders->items(),
                'pagination' => [
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('OrderController: fetchUserOrders - An error occurred.', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching user orders.',
            ], 500);
        }
    }
}

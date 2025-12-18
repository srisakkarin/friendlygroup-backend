<?php

namespace App\Http\Controllers;

use App\Models\CartItems;
use App\Models\Carts;
use App\Models\GlobalFunction;
use App\Models\OrderDetails;
use App\Models\Orders;
use App\Models\RevenueSharingRule;
use App\Models\ShopProduct;
use App\Models\ShopProductVariants;
use App\Models\ShopUser;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartsController extends Controller
{
    public function addToCart(Request $request)
    {
        $userId = $request->user_id;
        $user = Users::find($userId);
        $productId = $request->input('product_id');
        $variantId = $request->input('variant_id');
        $quantity = $request->input('quantity', 1);

        try {
            // Validate input
            $product = ShopProduct::findOrFail($productId);
            $price = $variantId ? ShopProductVariants::find($variantId)->pvar_price : $product->pro_price;

            // Get or create cart for the shop
            $shopId = $product->pro_shop_id;
            $cart = Carts::firstOrCreate([
                'user_id' => $user->id,
                'shop_id' => $shopId,
            ]);

            // Check if the item already exists in the cart
            $cartItem = CartItems::where('cart_id', $cart->id)
                ->where('product_id', $productId)
                ->where('variant_id', $variantId)
                ->first();

            if ($cartItem) {
                $cartItem->update(['quantity' => $cartItem->quantity + $quantity]);
            } else {
                CartItems::create([
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'shop_id' => $shopId,
                ]);
            }

            return response()->json(['message' => 'Item added to cart']);
        } catch (\Exception $e) {
            Log::error('Error adding item to cart: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to add item to cart', 'error' => $e->getMessage()], 500);
        }
    }

    public function getUserCart(Request $request)
    {
        $userId = $request->user_id;
        $user = Users::find($userId);

        try {
            // Get all carts for the user, grouped by shop
            $carts = Carts::with(['items.product', 'items.variant', 'shop'])
                ->where('user_id', $user->id)
                ->get();

            // Transform data to a more readable format
            $cartData = $carts->map(function ($cart) {
                return [
                    'shop' => [
                        'id' => $cart->shop->shop_id,
                        'name' => $cart->shop->shop_name,
                    ],
                    'items' => $cart->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->pro_name,
                            'product_images' => explode(',', $item->product->pro_image),
                            'variant_id' => $item->variant_id,
                            'variant_name' => $item->variant ? $item->variant->pvar_name1 : null,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'total_price' => $item->quantity * $item->price,
                        ];
                    }),
                    'total_amount' => $cart->items->sum(function ($item) {
                        return $item->quantity * $item->price;
                    }),
                ];
            });

            return response()->json([
                'message' => 'User cart retrieved successfully',
                'data' => $cartData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting user cart: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to get user cart', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateCartItemQuantity(Request $request)
    {
        $userId = $request->user_id;
        $user = Users::find($userId);
        $cartItemId = $request->input('cart_item_id');
        $action = $request->input('action'); // 'increase' or 'decrease'

        try {
            // Validate input
            $request->validate([
                'cart_item_id' => 'required|exists:cart_items,id',
                'action' => 'required|in:increase,decrease',
            ]);

            // Find the cart item
            $cartItem = CartItems::whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->findOrFail($cartItemId);

            // Update quantity based on action
            if ($action === 'increase') {
                $cartItem->increment('quantity');
            } elseif ($action === 'decrease') {
                if ($cartItem->quantity > 1) {
                    $cartItem->decrement('quantity');
                } else {
                    // If quantity is 1 and user decreases, delete the item
                    $cartItem->delete();
                    return response()->json(['message' => 'Item removed from cart']);
                }
            }

            return response()->json([
                'message' => 'Cart item quantity updated successfully',
                'cart_item' => $cartItem,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating cart item quantity: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update cart item quantity', 'error' => $e->getMessage()], 500);
        }
    }

    public function checkout(Request $request)
    {
        // Validate shipping address and payment method
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'shop_id' => 'required|exists:shop_openshop,shop_id',
            'shipping_address' => 'required|string',
            'payment_method' => 'required|string',
        ]);

        $userId = $request->user_id;
        $shopId = $request->shop_id;
        $user = Users::find($userId);
        $shop = ShopUser::where('shop_id', $shopId)->first();
        if (!$shop) {
            return response()->json(['message' => 'Shop not found'], 404);
        }
        $sallerId = $shop->shop_users_id; 
        
        $shippingAddress = $request->input('shipping_address');
        $paymentMethod = $request->input('payment_method');

        // Get the user's cart for the specified shop
        $cart = Carts::with('items.product', 'items.variant')
            ->where('user_id', $user->id)
            ->where('shop_id', $shopId)
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty for this shop'], 400);
        }

        // Calculate total amount
        $totalAmount = $cart->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        // check user wallet 
        $result = GlobalFunction::minusCoinsFromWallet($user->id, $totalAmount, 2, 11);
        if ($result->getStatusCode() !== 200) {
            return response()->json([
                'status' => false,
                'message' => $result->getData()->message,
            ], $result->getStatusCode());
        } else {
            // หักเงินใน wallet ของ user สำเร็จ
            // Create a new order
            $order = Orders::create([
                'user_id' => $user->id,
                'shop_id' => $shopId,
                'order_number' => 'ORD-' . uniqid(),
                'shipping_address' => $shippingAddress,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'payment_status' => 'paid',
                'order_status' => 'pending',
            ]);

            if (!$order) {
                return response()->json(['message' => 'Failed to create order'], 500);
            } else {
                // Add order details
                // เพิ่ม coin เข้า wallet ผู้ขาย
                // เพิ่ม coin เข้า wallet ของผู้รับ
                $sallerIncomeConfig = RevenueSharingRule::where('action_key', 'sell_item')->first();
                if ($sallerIncomeConfig->calculate_with === "percentage") {
                    $sallerIncome =  ($sallerIncomeConfig->customer_percent / 100) * $totalAmount;
                } else {
                    $sallerIncome = $sallerIncomeConfig->customer_amount;
                }

                $addCoinToWallet = GlobalFunction::addCoinsToWallet($sallerId, $sallerIncome, 1, 7);
                if ($addCoinToWallet->getStatusCode() !== 200) {
                    return response()->json([
                        'status' => false,
                        'message' => $addCoinToWallet->getData()->message,
                    ], $addCoinToWallet->getStatusCode());
                } else {
                    // Log success message
                    Log::info('CartController: add coin to wallet - Success [saller_id][' . $sallerId . ']');
                }
                foreach ($cart->items as $item) {
                    OrderDetails::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                    ]);
                }

                // Clear the cart after checkout
                $cart->items()->delete();
                $cart->delete();
            }
        }
        return response()->json([
            'message' => 'Order placed successfully',
            'order' => $order,
        ]);
    }
}

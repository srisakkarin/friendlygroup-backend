<?php

namespace App\Http\Controllers;

use App\Models\GlobalFunction;
use App\Models\ShopProduct;
use App\Models\ShopProductCategories;
use App\Models\ShopUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShopProductController extends Controller
{
    public function index(Request $request)
    {
        return view('products');
    }

    public function fetchAllProducts(Request $request)
    {
        try {
            // Get DataTables parameters
            $draw = $request->input('draw');
            $start = $request->input('start');
            $length = $request->input('length');
            $order = $request->input('order');
            $search = $request->input('search');
            $columns = $request->input('columns');

            // Base query with relationships
            $query = ShopProduct::query()
                ->with(['category', 'stock', 'images']);

            // --- Apply Search (Global Search) ---
            if (!empty($search['value'])) {
                $searchValue = $search['value'];
                $query->where(function ($q) use ($searchValue) {
                    $q->where('pro_name', 'like', "%{$searchValue}%")
                        ->orWhere('pro_price', 'like', "%{$searchValue}%")
                        ->orWhere('pro_min', 'like', "%{$searchValue}%")
                        ->orWhere('status', 'like', "%{$searchValue}%")
                        ->orWhere('visibility', 'like', "%{$searchValue}%")
                        // Search by category name
                        ->orWhereHas('category', function ($q) use ($searchValue) {
                            $q->where('cat_name', 'like', "%{$searchValue}%");
                        })
                        // Search by stock quantity (assuming tock_qty is the column)
                        ->orWhereHas('stock', function ($q) use ($searchValue) {
                            $q->where('tock_qty', 'like', "%{$searchValue}%");
                        });
                });
            }

            // Get total records before filtering
            $totalData = $query->count();

            // --- Apply Column-specific Search (if implemented on client-side) ---
            // You can add more complex logic here if your DataTables client-side
            // sends column-specific search values (e.g., columns[x][search][value])
            // For now, we'll focus on global search and correct ordering.

            // Get filtered records count
            $totalFiltered = $totalData; // Initialize with totalData, will update if specific search filters are applied

            // --- Apply Ordering ---
            if (!empty($order)) {
                $columnIndex = $order[0]['column'];
                $columnDir = $order[0]['dir']; // 'asc' or 'desc'
                $columnName = $columns[$columnIndex]['name']; // Get the 'name' property from client-side config

                // Map client-side 'name' to actual database columns for ordering
                switch ($columnName) {
                    case 'pro_name':
                    case 'pro_price':
                    case 'pro_min':
                    case 'status':
                    case 'visibility':
                    case 'pro_create':
                    case 'pro_id': // If you want to sort by ID, ensure it's selected in JS
                        $query->orderBy($columnName, $columnDir);
                        break;
                    case 'category_id': // Client-side name, sort by category name from related table
                        $query->join('shop_product_categories', 'shop_products.category_id', '=', 'shop_product_categories.category_id')
                            ->orderBy('shop_product_categories.cat_name', $columnDir)
                            ->select('shop_products.*'); // Select original product columns back to avoid ambiguity
                        break;
                    case 'stock_qty': // Client-side name, sort by stock quantity
                        $query->leftJoin('shop_product_stocks', 'shop_products.pro_id', '=', 'shop_product_stocks.tock_pro_id')
                            ->orderBy('shop_product_stocks.tock_qty', $columnDir)
                            ->select('shop_products.*'); // Select original product columns back
                        break;
                    // Add more cases for other sortable columns if needed
                    default:
                        // Default sorting if column name is not explicitly handled or for default DataTables behavior
                        // Fallback to pro_create if the default 'created_at' causes issues
                        $query->orderBy('pro_create', 'desc');
                        break;
                }
            } else {
                // Default order if no order is specified (e.g., initial load)
                $query->orderBy('pro_create', 'desc');
            }


            // --- Apply Pagination ---
            $query->offset($start)->limit($length);

            // Get the data
            $products = $query->get();

            $data = array();

            foreach ($products as $product) {
                $nestedData = [];
                $imageUrl = '';
                if ($product->images->isNotEmpty()) { // ตรวจสอบว่ามีรูปภาพหรือไม่
                    $firstImage = $product->images->first(); // ดึงรูปแรก
                    $imageUrl = $firstImage->image; // ดึง URL จากคอลัมน์ 'image' ใน ProductImage model
                    // เพิ่ม url('storage/') หากรูปภาพเป็น relative path
                    if (!str_starts_with($imageUrl, 'http') && !empty($imageUrl)) {
                        $imageUrl = url('storage/' . $imageUrl);
                    }
                }
                $nestedData['product_images'] = $imageUrl;

                // Column 1: Name
                $nestedData['pro_name'] = $product->pro_name;
                // Column 2: Price
                $nestedData['pro_price'] = $product->pro_price;
                // Column 3: Min Order
                $nestedData['pro_min'] = $product->pro_min;
                // Column 4: Category Name
                $nestedData['category_name'] = $product->category->name ?? '';
                // Column 5: Status
                $nestedData['status'] = $product->status;
                // Column 6: Visibility
                $nestedData['visibility'] = $product->visibility;
                // Column 7: Stock
                $nestedData['stock_qty'] = $product->stock->tock_qty ?? 0;
                // Column 8: Created At
                $nestedData['pro_create'] = $product->pro_create; // Use the actual column name from your DB
                // Column 9: Actions (handled by JS, just pass relevant IDs/data)
                $nestedData['pro_id'] = $product->pro_id; // Pass ID for actions
                $nestedData['pro_details'] = $product->pro_details; // Pass details for editing
                $nestedData['category_id'] = $product->category_id; // Pass category_id for editing
                $nestedData['product_images_raw'] = $product->images->toArray();
                $data[] = $nestedData;
            }

            $json_data = array(
                "draw" => intval($draw),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered), // This needs to reflect records after filtering
                "data" => $data
            );

            return response()->json($json_data);
        } catch (\Exception $e) {
            Log::error("ShopProductController: fetchAllProducts - An error occurred. " . json_encode(['error' => $e->getMessage(), 'request' => $request->all()]));
            return response()->json(['error' => 'An error occurred while fetching products.'], 500);
        }
    }

    public function allProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        // Check if user's shop exists
        $userShop = ShopUser::where('shop_users_id', $request->my_user_id)->first();
        if ($userShop === null) {
            return response()->json([
                'status' => false,
                'message' => 'User store not found!',
            ]);
        }

        try {
            $shopProducts = ShopProduct::with('variants', 'variants.stock', 'stock', 'category', 'images')
                ->where('pro_shop_id', $userShop->shop_id)
                // Optionally, filter by status or visibility for specific views
                // ->where('status', 'active') // Example: only active products
                // ->where('visibility', 'published') // Example: only published products
                ->get();
            return response()->json([
                'status' => true,
                'message' => 'Fetch Shop Product List',
                'data' => $shopProducts,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error Fetch Shop Product List',
                // 'data' => $shopMainCategory, // This line seems to be a leftover from a previous debug
            ], 500);
        }
    }

    public function getAllProductCategory(Request $request)
    {
        try {
            $productCategory = ShopProductCategories::all();
            if ($productCategory->isEmpty()) { // Use isEmpty() for collections
                return response()->json([
                    'status' => false,
                    'message' => 'No Data Found'
                ], 404);
            }
            return response()->json([
                'status' => true,
                'message' => 'Fetch Shop Product Category',
                'data' => $productCategory,
            ], 200);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error Fetch Shop Product Category'
            ], 500);
        }
    }

    public function getProductById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pro_id' => 'required|integer', // Added integer rule
        ]);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        try {
            $product = ShopProduct::with('variants', 'variants.stock', 'stock', 'images')->where('pro_id', $request->pro_id)->first();
            if (!$product) { // Check if product exists
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found',
                ], 404);
            }
            return response()->json([
                'status' => true,
                'message' => 'Fetch Shop Product',
                'data' => $product,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error Fetch Shop Product',
            ], 500);
        }
    }

    public function create(Request $request)
    {
        Log::info($request->all());
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
            'pro_name' => 'required|string|max:255', // Added string and max length
            'pro_details' => 'required|string',
            'pro_price' => 'required|numeric|min:0', // Added min:0
            'pro_min' => 'required|integer|min:1', // Added min:1
            // New validation rules for status and visibility
            'status' => 'nullable|in:pending,active,inactive',
            'visibility' => 'nullable|in:published,unpublished',
            'stock' => 'nullable|integer|min:0',
            'category_id' => 'required|exists:shop_product_categories,id',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string', // Assuming these are base64 strings or URLs
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $userShop = ShopUser::where('shop_users_id', $request->my_user_id)->first();
        if (!$userShop) {
            return response()->json([
                'status' => false,
                'message' => 'User store not found!',
            ], 404);
        }

        DB::beginTransaction(); // Start transaction
        try {
            $createdProduct = new ShopProduct();
            $createdProduct->pro_shop_id = $userShop->shop_id;
            $createdProduct->pro_mypro_id = $userShop->pro_mypro_id ?? ''; // Ensure this is correctly set if needed
            $createdProduct->pro_name = $request->pro_name;
            $createdProduct->pro_details = $request->pro_details;
            $createdProduct->pro_price = $request->pro_price;
            $createdProduct->pro_min = $request->pro_min;
            $createdProduct->pro_delete = 0; // Assuming 0 means not deleted
            $createdProduct->pro_image = null;
            $createdProduct->pro_status = 0;

            // Set new status and visibility, with defaults if not provided
            $createdProduct->status = $request->input('status', 'pending'); // Default to 'pending'
            $createdProduct->visibility = $request->input('visibility', 'unpublished'); // Default to 'unpublished'

            $createdProduct->category_id = $request->category_id;

            if ($createdProduct->save()) {
                // upload images and save url to product_images table
                if ($request->has('images') && is_array($request->images)) {
                    foreach ($request->images as $index => $image) {
                        $tmpKey = "image_" . $index;
                        $request->merge([$tmpKey => $image]); // Merge for GlobalFunction::uploadFileToS3 usage

                        try {
                            $path = GlobalFunction::uploadFileToS3($request, $tmpKey); // อัปโหลดรูปภาพ
                            if ($path) {
                                $createdProduct->images()->create([ // บันทึกลง ProductImage
                                    'image' => $path,
                                ]);
                            }
                        } catch (\Exception $e) {
                            DB::rollBack();
                            return response()->json([
                                'status' => false,
                                'message' => 'Error uploading image: ' . $e->getMessage(),
                            ], 500);
                        }
                    }
                }

                if ($request->has('stock')) {
                    // Check if stock relationship exists and model name is correct for 'stock'
                    $createdProduct->stock()->create([
                        'tock_shop_id' => $userShop->shop_id,
                        'tock_pro_id' => $createdProduct->pro_id,
                        'tock_pvar_id' => 0, // Assuming 0 for the main product stock
                        'tcok_instock' => $request->stock,
                    ]);
                }
                DB::commit(); // Commit transaction
                return response()->json([
                    'status' => true,
                    'message' => 'Product created successfully',
                    'data' => $createdProduct->load('stock', 'images'),
                ]);
            }

            DB::rollBack(); // Rollback if product save fails
            return response()->json(['status' => false, 'message' => 'Error saving product'], 500);
        } catch (\Throwable $e) {
            DB::rollBack(); // Rollback on any general error
            Log::error("Product creation error: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
            'pro_id' => 'required|integer|exists:shop_products,pro_id', // Validate pro_id exists
            'pro_name' => 'nullable|string|max:255',
            'pro_details' => 'nullable|string',
            'pro_price' => 'nullable|numeric|min:0',
            'pro_min' => 'nullable|integer|min:1',
            // New validation rules for status and visibility (nullable as they might not always be updated)
            'status' => 'nullable|in:pending,active,inactive',
            'visibility' => 'nullable|in:published,unpublished',
            'stock' => 'nullable|integer|min:0',
            'category_id' => 'nullable|exists:shop_product_categories,id',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $userShop = ShopUser::where('shop_users_id', $request->my_user_id)->first();
        if (!$userShop) {
            return response()->json(['status' => false, 'message' => 'User store not found!'], 404);
        }

        DB::beginTransaction(); // Start transaction
        try {
            $product = ShopProduct::find($request->pro_id);
            if (!$product) {
                return response()->json(['status' => false, 'message' => 'Product not found'], 404);
            }

            // Ensure the product belongs to the user's shop for security
            if ($product->pro_shop_id !== $userShop->shop_id) {
                return response()->json(['status' => false, 'message' => 'Unauthorized: Product does not belong to your shop.'], 403);
            }

            $product->fill($request->only([
                'pro_name',
                'pro_details',
                'pro_price',
                'pro_min',
                'category_id'
            ]));

            // Update new status and visibility fields if provided
            if ($request->has('status')) {
                $product->status = $request->status;
            }
            if ($request->has('visibility')) {
                $product->visibility = $request->visibility;
            }

            if ($product->save()) {
                if ($request->has('images')) {
                    // ลบรูปภาพเก่าทั้งหมดที่เกี่ยวข้องกับสินค้านี้
                    $product->images()->delete();

                    // เพิ่มรูปภาพใหม่
                    foreach ($request->images as $index => $image) {
                        $tmpKey = "image_" . $index;
                        $request->merge([$tmpKey => $image]); // Merge for GlobalFunction::uploadFileToS3 usage

                        try {
                            $path = GlobalFunction::uploadFileToS3($request, $tmpKey);
                            if ($path) {
                                $product->images()->create([
                                    'image' => $path,
                                ]);
                            }
                        } catch (\Exception $e) {
                            DB::rollBack();
                            return response()->json([
                                'status' => false,
                                'message' => 'Error uploading image: ' . $e->getMessage(),
                            ], 500);
                        }
                    }
                }

                if ($request->has('stock')) {
                    $stock = $product->stock()->firstOrNew([
                        'tock_pro_id' => $product->pro_id,
                        'tock_pvar_id' => 0,
                    ]);
                    $stock->tock_shop_id = $userShop->shop_id;
                    $stock->tcok_instock = $request->stock;
                    $stock->save();
                }
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Product updated successfully',
                    'data' => $product->load('stock', 'images'),
                ]);
            }
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Error updating product'], 500);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Product update error: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateProductStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
            'pro_id' => 'required|integer|exists:shop_products,pro_id',
            'status' => 'nullable|in:pending,active,inactive',
            'visibility' => 'nullable|in:published,unpublished',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $userShop = ShopUser::where('shop_users_id', $request->my_user_id)->first();
        if (!$userShop) {
            return response()->json(['status' => false, 'message' => 'User store not found!'], 404);
        }

        try {
            $product = ShopProduct::find($request->pro_id);
            if (!$product) {
                return response()->json(['status' => false, 'message' => 'Product not found'], 404);
            }

            // Ensure the product belongs to the user's shop for security
            if ($product->pro_shop_id !== $userShop->shop_id) {
                return response()->json(['status' => false, 'message' => 'Unauthorized: Product does not belong to your shop.'], 403);
            }

            // Only update if the status or visibility is provided in the request
            if ($request->has('status')) {
                $product->status = $request->status;
            }
            if ($request->has('visibility')) {
                $product->visibility = $request->visibility;
            }

            if ($product->isDirty()) { // Check if any changes were made before saving
                $product->save();
                return response()->json([
                    'status' => true,
                    'message' => 'Product status/visibility updated successfully',
                    'data' => $product,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'No changes detected for product status/visibility.',
                'data' => $product,
            ]);
        } catch (\Throwable $e) {
            Log::error("Update product status error: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'status' => false,
                'message' => 'Error updating product status/visibility: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
            'pro_id' => 'required|integer|exists:shop_products,pro_id', // Added integer and exists rule
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        try {
            // Find the product
            $product = ShopProduct::find($request->pro_id);
            // No need to check for existence here, as 'exists' validation rule handles it.

            $userShop = ShopUser::where('shop_users_id', $request->my_user_id)->first();
            if (!$userShop) {
                return response()->json(['status' => false, 'message' => 'User store not found!'], 404);
            }

            // Ensure the product belongs to the user's shop for security
            if ($product->pro_shop_id !== $userShop->shop_id) {
                return response()->json(['status' => false, 'message' => 'Unauthorized: Product does not belong to your shop.'], 403);
            }

            // Using soft delete if 'pro_delete' is meant for that, otherwise actual delete
            // Based on your migration, 'pro_delete' is an integer, so you might be doing a soft delete
            // If pro_delete is 0 (not deleted) and 1 (deleted), then modify this.
            // For now, I'm assuming you want to actually delete it given the previous code.
            DB::beginTransaction();
            if ($product->variants()->delete()) {
                // Delete product stock related to this product and its variants
                // The `where('tock_pro_id', $product->pro_id)` covers both main product stock and variant stock
                DB::table('shop_product_stock')->where('tock_pro_id', $product->pro_id)->delete();
            }

            // Delete the product itself
            $product->delete();
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Product deleted successfully',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Product deletion error: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateVariants(Request $request)
    {
        // Validate input data
        $validated = $request->validate([
            'pro_id' => 'required|integer|exists:shop_products,pro_id', // ID of the product
            'my_user_id' => 'required', // Added for shop ownership check
            'variants' => 'required|array',
            'variants.*.pvar_name1' => 'required|string|max:255',
            'variants.*.pvar_price' => 'required|numeric|min:0',
            'variants.*.tcok_instock' => 'nullable|integer|min:0',
        ]);

        // Find the product
        $product = ShopProduct::where('pro_id', $request->pro_id)->first();
        // No need to check for existence here, as 'exists' validation rule handles it.

        $userShop = ShopUser::where('shop_users_id', $request->my_user_id)->first();
        if (!$userShop) {
            return response()->json(['status' => false, 'message' => 'User store not found!'], 404);
        }

        // Ensure the product belongs to the user's shop for security
        if ($product->pro_shop_id !== $userShop->shop_id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized: Product does not belong to your shop.'], 403);
        }

        DB::beginTransaction(); // Start transaction
        try {
            // Delete existing variants and stock (overwrite with new data)
            // Ensure these are proper eloquent relations and cascade delete is not handled by DB directly
            $product->variants()->delete();
            // Delete product stock related to this product and its variants
            DB::table('shop_product_stock')->where('tock_pro_id', $product->pro_id)->delete();

            $productStockQuantity = 0;

            // Create new variants and stock with auto-incremented pvar_n1
            foreach ($validated['variants'] as $index => $variantData) {
                // Auto-generate pvar_n1 starting from 1 (or based on existing logic)
                $pvar_n1 = $index + 1;

                // Create variant
                $variant = $product->variants()->create([
                    'pvar_shop_id' => $product->pro_shop_id,
                    'pvar_n1' => $pvar_n1, // Auto-incremented value
                    'pvar_name1' => $variantData['pvar_name1'],
                    'pvar_n2' => 0, // Assuming static for now
                    'pvar_name2' => '', // Assuming static for now
                    'pvar_price' => $variantData['pvar_price'],
                    'pvar_sku' => '' // If SKU generation is needed, implement it here
                ]);

                // Create stock for the variant
                $variant->stock()->create([
                    'tock_shop_id' => $product->pro_shop_id,
                    'tock_pro_id' => $product->pro_id,
                    'tock_pvar_id' => $variant->pvar_id,
                    'tcok_instock' => $variantData['tcok_instock'] ?? 0,
                ]);

                $productStockQuantity += ($variantData['tcok_instock'] ?? 0);
            }

            // Update or create main product stock (tock_pvar_id = 0)
            $mainProductStock = $product->stock()->firstOrNew([
                'tock_pro_id' => $product->pro_id,
                'tock_pvar_id' => 0, // Main product stock
            ]);
            $mainProductStock->tock_shop_id = $product->pro_shop_id;
            $mainProductStock->tcok_instock = $productStockQuantity;
            $mainProductStock->save();

            DB::commit(); // Commit transaction

            return response()->json([
                'status' => true,
                'message' => 'Variants updated successfully',
                'data' => $product->variants()->with('stock')->get(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack(); // Rollback on error
            Log::error("Update variants error: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function addProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pro_name' => 'required|string|max:255',
            'pro_details' => 'required|string',
            'pro_price' => 'required|numeric|min:0',
            'pro_min' => 'required|integer|min:1',
            'category_id' => 'nullable|exists:shop_product_categories,id', // ตรวจสอบว่า category_id มีอยู่จริงในตารางหมวดหมู่
            'status' => 'required|in:pending,active,inactive',
            'visibility' => 'required|in:unpublished,published',
            'stock' => 'nullable|integer|min:0',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // ตรวจสอบแต่ละรูปภาพ
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => __('app.Error'),
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $product = new ShopProduct();
            $product->pro_name = $request->pro_name;
            $product->pro_details = $request->pro_details;
            $product->pro_price = $request->pro_price;
            $product->pro_min = $request->pro_min;
            $product->category_id = $request->category_id;
            $product->status = $request->status;
            $product->visibility = $request->visibility;
              
            if($product->save()) {
                 if ($request->has('images')) {
                    
                    // เพิ่มรูปภาพใหม่
                    foreach ($request->images as $index => $image) {
                        
                        try {
                            // $path = GlobalFunction::uploadFileToS3($image, $tmpKey);
                            $path = GlobalFunction::saveFileAndGivePath($image);
                            if ($path) {
                                $product->images()->create([
                                    'image' => $path,
                                ]);
                            }
                        } catch (\Exception $e) {
                            DB::rollBack();
                            return response()->json([
                                'status' => false,
                                'message' => 'Error uploading image: ' . $e->getMessage(),
                            ], 500);
                        }
                    }
                }
            }

            return response()->json([
                'status' => 200,
                'message' => __('app.AddSuccessful')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('app.Something_went_wrong') . ': ' . $e->getMessage()
            ], 500);
        }
    }
    public function updateProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pro_id' => 'required|exists:shop_products,pro_id',
            'pro_name' => 'required|string|max:255',
            'pro_details' => 'required|string',
            'pro_price' => 'required|numeric|min:0',
            'pro_min' => 'required|integer|min:1',
            'category_id' => 'nullable|exists:shop_product_categories,id',
            'status' => 'required|in:pending,active,inactive',
            'visibility' => 'required|in:unpublished,published',
            'stock' => 'nullable|integer|min:0',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        Log::info($request->all());

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => __('app.Error'),
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $product = ShopProduct::find($request->pro_id);
            if (!$product) {
                return response()->json([
                    'status' => 404,
                    'message' => __('app.UserNotFound') // สามารถเปลี่ยนเป็น 'ProductNotFound' ได้
                ], 404);
            }

            $product->pro_name = $request->pro_name;
            $product->pro_details = $request->pro_details;
            $product->pro_price = $request->pro_price;
            $product->pro_min = $request->pro_min;
            $product->category_id = $request->category_id;
            $product->status = $request->status;
            $product->visibility = $request->visibility; 

            if ($product->save()) {
                if ($request->has('images')) {
                    // ลบรูปภาพเก่าทั้งหมดที่เกี่ยวข้องกับสินค้านี้
                    $product->images()->delete();

                    // เพิ่มรูปภาพใหม่
                    foreach ($request->images as $index => $image) {
                        $tmpKey = "image_" . $index;
                        // $request->merge([$tmpKey => $image]); // Merge for GlobalFunction::uploadFileToS3 usage

                        try {
                            // $path = GlobalFunction::uploadFileToS3($image, $tmpKey);
                            $path = GlobalFunction::saveFileAndGivePath($image);
                            if ($path) {
                                $product->images()->create([
                                    'image' => $path,
                                ]);
                            }
                        } catch (\Exception $e) {
                            DB::rollBack();
                            return response()->json([
                                'status' => false,
                                'message' => 'Error uploading image: ' . $e->getMessage(),
                            ], 500);
                        }
                    }
                }
            }

            return response()->json([
                'status' => 200,
                'message' => __('app.Updatesuccessful')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('app.Something_went_wrong') . ': ' . $e->getMessage()
            ], 500);
        }
    }
    public function deleteProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pro_id' => 'required|exists:shop_products,pro_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => __('app.Error'),
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $product = ShopProduct::find($request->pro_id);
            if (!$product) {
                return response()->json([
                    'status' => 404,
                    'message' => __('app.UserNotFound') // สามารถเปลี่ยนเป็น 'ProductNotFound' ได้
                ], 404);
            } 
            $product->delete();

            return response()->json([
                'status' => 200,
                'message' => __('app.Delete') . ' ' . __('app.Successful') // สามารถสร้างคีย์ 'DeleteSuccessful' ได้
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('app.Something_went_wrong') . ': ' . $e->getMessage()
            ], 500);
        }
    }
}

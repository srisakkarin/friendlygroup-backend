<?php

namespace App\Http\Controllers;

use App\Models\GlobalFunction;
use App\Models\ShopMainCategory;
use App\Models\ShopProductCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShopProductCategoriesController extends Controller
{
    public function productCategories()
    { 
        return view('productcategories');
    }

    public function fetchAllProductCateories(Request $request)
    {
        try {
            $totalData = ShopProductCategories::count();
            $rows = ShopProductCategories::orderBy('id', 'DESC')->get();

            $result = $rows;

            $columns = array(
                0 => 'id',
                1 => 'name'
            );

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
            $totalData = ShopProductCategories::count();
            $totalFiltered = $totalData;

            if (empty($request->input('search.value'))) { 
                $result = ShopProductCategories::offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
            } else {
                $search = $request->input('search.value'); 
                $result = ShopProductCategories::where('name', 'LIKE', "%{$search}%") // Corrected: Changed 'coin_price' to 'name'
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
                $totalFiltered = ShopProductCategories::where('name', 'LIKE', "%{$search}%") // Corrected: Changed 'coin_price' to 'name'
                    ->count();
            }

            $data = array();
            foreach ($result as $item) {
                $image = '<img src="' . $item->image . '" width="50" height="50">';
                $imgUrl = env('image') . $item->image;

                if (empty($item->image)) {
                    $image = '<img src="https://cdn-icons-png.flaticon.com/512/8136/8136031.png" width="50" height="50">';
                    $imgUrl = '';
                }

                $action = '<a data-img="' . $imgUrl . '" 
                           data-name="' . $item->name . '" 
                           data-description="' . $item->description . '"
                           rel="' . $item->id . '" 
                           class="btn btn-success edit text-white mr-2">
                           Edit
                           </a>
                           <a rel="' . $item->id . '"
                           class="btn btn-danger delete text-white">
                           Delete
                           </a>';

                $data[] = array(
                    $image,
                    $item->name,
                    $item->description,
                    $action
                );
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => $totalFiltered,
                "data" => $data
            );
            Log::info('ShopProductCategoriesController: fetchAllProductCateories - Data fetched and formatted successfully.');
            echo json_encode($json_data);
            exit();
        } catch (\Exception $e) {
            Log::error('ShopProductCategoriesController: fetchAllProductCateories - An error occurred.', ['error' => $e->getMessage(), 'request' => $request->all()]);
            echo json_encode(["error" => "An error occurred while fetching product categories."]);
            exit();
        }
    }

    function addProductCategory(Request $request)
    { 
        try {
            $category = new ShopProductCategories();
            $category->image = GlobalFunction::saveFileAndGivePath($request->image);
            $category->name = $request->name;
            $category->description = $request->description;
            $category->save();

            Log::info('ShopProductCategoriesController: addProductCategory - Category added successfully.', ['category_id' => $category->id]);

            return response()->json([
                'status' => true,
                'message' => "Category Added Successfully.",
                'data' => $category,
            ]);
        } catch (\Exception $e) {
            Log::error('ShopProductCategoriesController: addProductCategory - An error occurred.', ['error' => $e->getMessage(), 'request' => $request->all()]);
            return response()->json([
                'status' => false,
                'message' => "An error occurred while adding the category.",
            ], 500);
        }
    }

    public function updateProductCategory(Request $request)
    {
         
        try {
            $category = ShopProductCategories::where('id', $request->id)->first();
            if (!$category) {
                Log::warning('ShopProductCategoriesController: updateProductCategory - Category not found.', ['category_id' => $request->id]);
                return response()->json([
                    'status' => false,
                    'message' => 'Category not found.',
                ], 404);
            }
            $category->name = $request->name;
            $category->description = $request->description;
            if ($request->has('image')) {
                Log::info('ShopProductCategoriesController: updateProductCategory - New image provided.', ['old_image' => $category->image]);
                GlobalFunction::deleteFile($category->image);
                $category->image = GlobalFunction::saveFileAndGivePath($request->image);
            }
            $category->save();

            Log::info('ShopProductCategoriesController: updateProductCategory - Category updated successfully.', ['category_id' => $category->id]);

            return response()->json([
                'status' => true,
                'message' => 'Category Update Successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('ShopProductCategoriesController: updateProductCategory - An error occurred.', ['error' => $e->getMessage(), 'request' => $request->all()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the category.',
            ], 500);
        }
    }

    public function deleteProductCategory(Request $request)
    { 
        try {
            $category = ShopProductCategories::where('id', $request->category_id)->first();
            if (!$category) {
                Log::warning('ShopProductCategoriesController: deleteProductCategory - Category not found.', ['category_id' => $request->category_id]);
                return response()->json([
                    'status' => false,
                    'message' => 'Category not found.',
                ], 404);
            }
            Log::info('ShopProductCategoriesController: deleteProductCategory - Deleting category.', ['category_id' => $category->id, 'image' => $category->image]);
            GlobalFunction::deleteFile($category->image);
            $category->delete();

            Log::info('ShopProductCategoriesController: deleteProductCategory - Category deleted successfully.', ['category_id' => $request->category_id]);

            return response()->json([
                'status' => true,
                'message' => 'Category Delete Successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('ShopProductCategoriesController: deleteProductCategory - An error occurred.', ['error' => $e->getMessage(), 'request' => $request->all()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the category.',
            ], 500);
        }
    }
}

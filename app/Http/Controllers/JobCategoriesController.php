<?php

namespace App\Http\Controllers;

use App\Models\GlobalFunction;
use App\Models\JobCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class JobCategoriesController extends Controller
{
    public function jobCategories()
    { 
        return view('jobcategories');
    }

    public function fetchAllJobCateories(Request $request)
    {
        
        try {
            $totalData = JobCategories::count();
            $rows = JobCategories::orderBy('id', 'DESC')->get();

            $result = $rows;

            $columns = array(
                0 => 'id',
                1 => 'name'
            );

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
            $totalData = JobCategories::count();
            $totalFiltered = $totalData;

            if (empty($request->input('search.value'))) {
                $result = JobCategories::offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
            } else {
                $search = $request->input('search.value'); 
                $result = JobCategories::where('name', 'LIKE', "%{$search}%") // Corrected: Changed 'coin_price' to 'name'
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
                $totalFiltered = JobCategories::where('name', 'LIKE', "%{$search}%") // Corrected: Changed 'coin_price' to 'name'
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

            Log::info('JobCategoriesController: fetchAllJobCateories - Data fetched and formatted successfully.');
            echo json_encode($json_data);
            exit();

        } catch (\Exception $e) {
            Log::error('JobCategoriesController: fetchAllJobCateories - An error occurred.', ['error' => $e->getMessage(), 'request' => $request->all()]);
            echo json_encode(["error" => "An error occurred while fetching job categories."]);
            exit();
        }
    }

    function addJobCategory(Request $request)
    {
        
        try {
            $category = new JobCategories();
            $category->image = GlobalFunction::saveFileAndGivePath($request->image);
            $category->name = $request->name;
            $category->description = $request->description;
            $category->save();

            Log::info('JobCategoriesController: addJobCategory - Category added successfully.', ['category_id' => $category->id]);

            return response()->json([
                'status' => true,
                'message' => "Category Added Successfully.",
                'data' => $category,
            ]);
        } catch (\Exception $e) {
            Log::error('JobCategoriesController: addJobCategory - An error occurred.', ['error' => $e->getMessage(), 'request' => $request->all()]);
            return response()->json([
                'status' => false,
                'message' => "An error occurred while adding the category.",
            ], 500);
        }
    }

    public function updateJobCategory(Request $request)
    {
        
        try {
            $category = JobCategories::where('id', $request->id)->first();
            if (!$category) {
                Log::warning('JobCategoriesController: updateJobCategory - Category not found.', ['category_id' => $request->id]);
                return response()->json([
                    'status' => false,
                    'message' => 'Category not found.',
                ], 404);
            }
            $category->name = $request->name;
            $category->description = $request->description;
            if ($request->has('image')) {
                Log::info('JobCategoriesController: updateJobCategory - New image provided.', ['old_image' => $category->image]);
                GlobalFunction::deleteFile($category->image);
                $category->image = GlobalFunction::saveFileAndGivePath($request->image);
            }
            $category->save();

            Log::info('JobCategoriesController: updateJobCategory - Category updated successfully.', ['category_id' => $category->id]);

            return response()->json([
                'status' => true,
                'message' => 'Category Update Successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('JobCategoriesController: updateJobCategory - An error occurred.', ['error' => $e->getMessage(), 'request' => $request->all()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the category.',
            ], 500);
        }
    }

    public function deleteJobCategory(Request $request)
    {
         
        try {
            $category = JobCategories::where('id', $request->category_id)->first();
            if (!$category) {
                Log::warning('JobCategoriesController: deleteJobCategory - Category not found.', ['category_id' => $request->category_id]);
                return response()->json([
                    'status' => false,
                    'message' => 'Category not found.',
                ], 404);
            }
            Log::info('JobCategoriesController: deleteJobCategory - Deleting category.', ['category_id' => $category->id, 'image' => $category->image]);
            GlobalFunction::deleteFile($category->image);
            $category->delete();

            Log::info('JobCategoriesController: deleteJobCategory - Category deleted successfully.', ['category_id' => $request->category_id]);

            return response()->json([
                'status' => true,
                'message' => 'Category Delete Successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('JobCategoriesController: deleteJobCategory - An error occurred.', ['error' => $e->getMessage(), 'request' => $request->all()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the category.',
            ], 500);
        }
    }
}

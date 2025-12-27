<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Models\GlobalFunction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RewardController extends Controller
{
    public function index()
    {
        return view('rewards');
    }

    public function getRewards(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $rewards = Reward::orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'status' => true,
                'message' => 'Rewards fetched successfully.',
                'data' => $rewards->items(),
                'pagination' => [
                    'total' => $rewards->total(),
                    'per_page' => $rewards->perPage(),
                    'current_page' => $rewards->currentPage(),
                    'last_page' => $rewards->lastPage(),
                    'from' => $rewards->firstItem(),
                    'to' => $rewards->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('RewardController: getRewards - An error occurred.', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching rewards.',
            ], 500);
        }
    }

    public function getRewardById(Request $request)
    {
        try {
            $id = $request->id;
            $reward = Reward::find($id);

            if (!$reward) {
                return response()->json([
                    'status' => false,
                    'message' => 'Reward not found.',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Reward fetched successfully.',
                'data' => $reward,
            ]);
        } catch (\Exception $e) {
            Log::error('RewardController: getRewardById - An error occurred.', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching the reward.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'required_points' => 'required|integer|min:1',
            'type' => 'required|in:gift,discount',
            'discount_type' => 'nullable|required_if:type,discount|in:percent,fixed',
            'discount_value' => 'nullable|required_if:type,discount|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $reward = new Reward();
            $reward->name = $request->name;
            $reward->required_points = $request->required_points;
            $reward->type = $request->type;
            $reward->description = $request->description;
            $reward->is_active = $request->input('is_active', 1);

            // Handle Discount Logic
            if ($request->type === 'discount') {
                $reward->discount_type = $request->discount_type;
                $reward->discount_value = $request->discount_value;
            }

            // Handle Image Upload via GlobalFunction
            if ($request->has('image')) {
                $reward->image = GlobalFunction::saveFileAndGivePath($request->image);
            }

            $reward->save();

            Log::info('RewardController: store - Reward created successfully.', ['id' => $reward->id]);

            return response()->json([
                'status' => true,
                'message' => 'Reward Added Successfully.',
                'data' => $reward
            ]);

        } catch (\Exception $e) {
            Log::error('RewardController: store - An error occurred.', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the reward.',
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:rewards,id',
            'name' => 'required|string|max:255',
            'required_points' => 'required|integer|min:1',
            'type' => 'required|in:gift,discount',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $reward = Reward::where('id', $request->id)->first();
            
            $reward->name = $request->name;
            $reward->required_points = $request->required_points;
            $reward->type = $request->type;
            $reward->description = $request->description;
            $reward->is_active = $request->is_active;

            // Handle Discount Logic Update
            if ($request->type === 'discount') {
                $reward->discount_type = $request->discount_type;
                $reward->discount_value = $request->discount_value;
            } else {
                // If switching to gift, clear discount fields
                $reward->discount_type = null;
                $reward->discount_value = null;
            }

            // Handle Image Update via GlobalFunction
            if ($request->has('image')) {
                GlobalFunction::deleteFile($reward->image);
                $reward->image = GlobalFunction::saveFileAndGivePath($request->image);
            }

            $reward->save();

            Log::info('RewardController: update - Reward updated successfully.', ['id' => $reward->id]);

            return response()->json([
                'status' => true,
                'message' => 'Reward Update Successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('RewardController: update - An error occurred.', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the reward.',
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            // ใช้ gift_id หรือ id ตามที่ JS ส่งมา (ใน rewards.js ที่เขียนไว้ส่ง id)
            $id = $request->id; 
            
            $reward = Reward::where('id', $id)->first();

            if (!$reward) {
                return response()->json([
                    'status' => false,
                    'message' => 'Reward not found.',
                ], 404);
            }

            // Delete image via GlobalFunction
            if ($reward->image) {
                GlobalFunction::deleteFile($reward->image);
            }

            $reward->delete();

            Log::info('RewardController: destroy - Reward deleted successfully.', ['id' => $id]);

            return response()->json([
                'status' => true,
                'message' => 'Reward Delete Successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('RewardController: destroy - An error occurred.', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the reward.',
            ], 500);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\GlobalFunction;
use App\Models\WorkerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WorkerProfileController extends Controller
{
    // Existing createWorkerProfile with corrections for single image handling
    public function createWorkerProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
            'bio' => 'nullable|string',
            'images' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $workerProfile = new WorkerProfile();
            $workerProfile->user_id = $request->my_user_id;
            $workerProfile->bio = $request->bio;

            if ($request->hasFile('images')) {
                $path = GlobalFunction::saveFileAndGivePath($request->file('images'));
                if ($path) {
                    $workerProfile->profile_picture = $path;
                } else {
                    return response()->json(['status' => false, 'message' => 'Image upload failed'], 500);
                }
            }

            if ($workerProfile->save()) {
                Log::info('Worker Profile Created: ' . $workerProfile->id);
                return response()->json([
                    'status' => true,
                    'message' => 'Profile created successfully',
                    'data' => $workerProfile
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Create Profile Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Server error'], 500);
        }
    }

    // Update Worker Profile
    public function updateWorkerProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
            'bio' => 'nullable|string',
            'images' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $workerProfile = WorkerProfile::where('user_id', $request->my_user_id)->first();
            
            if (!$workerProfile) {
                return response()->json(['status' => false, 'message' => 'Profile not found'], 404);
            }

            if ($request->has('bio')) {
                $workerProfile->bio = $request->bio;
            }

            if ($request->hasFile('images')) {
                // Delete old image
                if ($workerProfile->profile_picture) {
                    GlobalFunction::deleteFile($workerProfile->profile_picture);
                }
                
                $path = GlobalFunction::saveFileAndGivePath($request->file('images'));
                if ($path) {
                    $workerProfile->profile_picture = $path;
                } else {
                    return response()->json(['status' => false, 'message' => 'Image update failed'], 500);
                }
            }

            if ($workerProfile->save()) {
                Log::info('Worker Profile Updated: ' . $workerProfile->id);
                return response()->json([
                    'status' => true,
                    'message' => 'Profile updated successfully',
                    'data' => $workerProfile
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Update Profile Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Server error'], 500);
        }
    }

    // Delete Worker Profile
    public function deleteWorkerProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $workerProfile = WorkerProfile::where('user_id', $request->my_user_id)->first();
            
            if (!$workerProfile) {
                return response()->json(['status' => false, 'message' => 'Profile not found'], 404);
            }

            // Delete associated image
            if ($workerProfile->profile_picture) {
                GlobalFunction::deleteFile($workerProfile->profile_picture);
            }

            if ($workerProfile->delete()) {
                Log::info('Worker Profile Deleted: ' . $workerProfile->id);
                return response()->json([
                    'status' => true,
                    'message' => 'Profile deleted successfully'
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Delete Profile Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Server error'], 500);
        }
    }

    // Get Worker Profile by User ID
    public function getWorkerProfileByUserId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $workerProfile = WorkerProfile::with('user')
                ->where('user_id', $request->my_user_id)
                ->first();

            if (!$workerProfile) {
                return response()->json(['status' => false, 'message' => 'Profile not found'], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Profile retrieved successfully',
                'data' => $workerProfile
            ]);
        } catch (\Throwable $e) {
            Log::error('Get Profile Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Server error'], 500);
        }
    }
}
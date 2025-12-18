<?php

namespace App\Http\Controllers;

use App\Models\GlobalFunction;
use App\Models\Job;
use App\Models\JobCategories;
use App\Models\JobImage;
use App\Models\WorkerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    public function createJob(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'starting_price' => 'required',
            'status' => 'required|string|in:draft,public',
            'category_id' => 'required|exists:job_categories,id', // เพิ่ม validation สำหรับ category_id
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        $workerProfile = WorkerProfile::where('user_id', $request->my_user_id)->first();
        if ($workerProfile === null) {
            return response()->json([
                'status' => false,
                'message' => 'Worker profile not found!',
            ], 404); // 404 Not Found
        }

        try {
            // Create a new job instance
            $createJob = new Job();
            $createJob->user_id = $request->my_user_id;
            $createJob->worker_profile_id = $workerProfile->id;
            $createJob->title = $request->title;
            $createJob->description = $request->description;
            $createJob->starting_price = $request->starting_price;
            $createJob->status = $request->status;
            $createJob->category_id = $request->category_id; // เพิ่ม category_id

            if ($createJob->save()) {
                // Handle image uploads
                if ($request->hasFile('images')) {
                    $files = $request->file('images');
                    if (is_array($files)) {
                        foreach ($files as $file) {
                            $jobImages = new JobImage();
                            $jobImages->job_id = $createJob->id;
                            $path = GlobalFunction::saveFileAndGivePath($file);
                            $jobImages->image = $path;
                            $jobImages->save();
                        }
                    } else {
                        // Handle single file upload
                        $jobImages = new JobImage();
                        $jobImages->job_id = $createJob->id;
                        $path = GlobalFunction::saveFileAndGivePath($files);
                        $jobImages->image = $path;
                        $jobImages->save();
                    }
                }

                Log::info('Job Created: ' . $createJob->id);
                return response()->json([
                    'status' => true,
                    'message' => 'Job created successfully',
                    'data' => $createJob->load('images')
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Create Job Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Server error'], 500);
        }
    }

    public function updateJob(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|exists:jobs,id',
            'my_user_id' => 'required',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'starting_price' => 'nullable|numeric',
            'status' => 'nullable|string|in:draft,public',
            'category_id' => 'nullable|exists:job_categories,id', // เพิ่ม validation สำหรับ category_id
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $job = Job::where('id', $request->job_id)
                ->where('user_id', $request->my_user_id)
                ->first();

            if (!$job) {
                return response()->json(['status' => false, 'message' => 'Job not found or unauthorized'], 404);
            }

            // Update job fields
            if ($request->has('title')) $job->title = $request->title;
            if ($request->has('description')) $job->description = $request->description;
            if ($request->has('starting_price')) $job->starting_price = $request->starting_price;
            if ($request->has('status')) $job->status = $request->status;
            if ($request->has('category_id')) $job->category_id = $request->category_id; // อัปเดต category_id

            $job->save();

            // Handle new images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path = GlobalFunction::saveFileAndGivePath($file);
                    if ($path) {
                        JobImage::create([
                            'job_id' => $job->id,
                            'image' => $path
                        ]);
                    }
                }
            }

            Log::info('Job Updated: ' . $job->id);
            return response()->json([
                'status' => true,
                'message' => 'Job updated successfully',
                'data' => $job->load('images')
            ]);
        } catch (\Throwable $e) {
            Log::error('Update Job Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Server error'], 500);
        }
    }

    public function deleteJob(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|exists:jobs,id',
            'my_user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $job = Job::where('id', $request->job_id)
                ->where('user_id', $request->my_user_id)
                ->first();

            if (!$job) {
                return response()->json(['status' => false, 'message' => 'Job not found or unauthorized'], 404);
            }

            // Delete associated images
            foreach ($job->images as $image) {
                GlobalFunction::deleteFile($image->image);
                $image->delete();
            }

            $job->delete();

            Log::info('Job Deleted: ' . $job->id);
            return response()->json([
                'status' => true,
                'message' => 'Job deleted successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('Delete Job Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Server error'], 500);
        }
    }

    public function deleteJobImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_image_id' => 'required|exists:job_images,id',
            'my_user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $image = JobImage::where('id', $request->job_image_id)
                ->whereHas('job', function ($q) use ($request) {
                    $q->where('user_id', $request->my_user_id);
                })
                ->first();

            if (!$image) {
                return response()->json(['status' => false, 'message' => 'Image not found or unauthorized'], 404);
            }

            GlobalFunction::deleteFile($image->image);
            $image->delete();

            Log::info('Job Image Deleted: ' . $request->job_image_id);
            return response()->json([
                'status' => true,
                'message' => 'Image deleted successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('Delete Image Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Server error'], 500);
        }
    }

    public function getJobByJobId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|exists:jobs,id',
            'my_user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $job = Job::with('images')
                ->where('id', $request->job_id)
                ->where('user_id', $request->my_user_id)
                ->first();

            if (!$job) {
                return response()->json(['status' => false, 'message' => 'Job not found or unauthorized'], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Job retrieved successfully',
                'data' => $job
            ]);
        } catch (\Throwable $e) {
            Log::error('Get Job Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Server error'], 500);
        }
    }

    public function getJobByUserId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $jobs = Job::with('images')
                ->where('user_id', $request->my_user_id)
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Jobs retrieved successfully',
                'data' => $jobs
            ]);
        } catch (\Throwable $e) {
            Log::error('Get Jobs Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Server error'], 500);
        }
    }

    public function getAllJobCategory(Request $request)
    {
        try {
            $jobCategory = JobCategories::all();
            if (empty($jobCategory)) {
                return response()->json(['status' => false, 'message' => 'No Data Found'], 404);
            }
            return response()->json([
                'status' => true,
                'message' => 'Fetch Job Category',
                'data' => $jobCategory
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Get Job Category Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Server error'], 500);
        }
    }
}

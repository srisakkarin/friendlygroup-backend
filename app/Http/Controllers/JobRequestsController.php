<?php

namespace App\Http\Controllers;

use App\Models\GlobalFunction;
use App\Models\JobRequests;
use App\Models\JobRequestFiles;
use App\Models\WorkerProfile;
use App\Models\Users;
use App\Models\Job;
use App\Models\Myfunction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class JobRequestsController extends Controller
{
    public function createJobRequest(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'worker_profile_id' => 'required|exists:worker_profiles,id',
            'job_id' => 'required|exists:jobs,id',
            'description' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Create the job request
        try {
            $jobRequest = JobRequests::create([
                'user_id' => $data['user_id'],
                'worker_profile_id' => $data['worker_profile_id'],
                'job_id' => $data['job_id'],
                'description' => $data['description'] ?? null,
                'status' => 'pending',
            ]);
        } catch (\Exception $e) {
            Log::error("Error creating JobRequest: " . $e->getMessage());
            return response()->json(['message' => 'Failed to create job request.'], 500);
        }

        // Upload files (if any)
        if ($request->hasFile('files')) {
            try {
                foreach ($request->file('files') as $file) {
                    $path = GlobalFunction::saveFileAndGivePath($file);

                    // Create file records in the job_request_files table
                    $jobRequest->files()->create([
                        'file_path' => $path,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Error uploading files for JobRequest ID {$jobRequest->id}: " . $e->getMessage());
                // Clean up the created JobRequest if file upload fails
                $jobRequest->delete();
                return response()->json(['message' => 'Failed to upload files.'], 500);
            }
        }

        // Send notification to the worker (if notification system is implemented)
        // $workerProfile = WorkerProfile::find($data['worker_profile_id']);
        // if ($workerProfile && $workerProfile->user) {
        //     $user = Users::find($workerProfile->user->id);
        //     if($user && $user->is_notification == 1){
        //         $notificationDesc = 'You have a new job request.';
        //         Myfunction::sendPushToUser(env('APP_NAME'), $notificationDesc, $user->device_token);
        //     }
        // }

        return response()->json([
            'message' => 'Job request submitted successfully.',
            'job_request_id' => $jobRequest->id,
        ], 201);
    }

    public function getCustomerJobRequests(Request $request)
    {
        // Validate the request to ensure user_id exists in the users table
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'status' => 'nullable|in:pending,accepted,process,success,rejected', // Allow filtering by status
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Start building the query
        $query = JobRequests::where('user_id', $request->user_id);

        // Apply status filter if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Retrieve job requests with related data
        $jobRequests = $query->with(['user', 'workerProfile', 'job', 'files'])->get();

        return response()->json([
            'message' => 'Customer job requests retrieved successfully.',
            'data' => $jobRequests,
        ], 200);
    }

    public function getWorkerJobRequests(Request $request)
    {
        // Validate the request to ensure worker_profile_id exists in the worker_profiles table
        $validator = Validator::make($request->all(), [
            'worker_profile_id' => 'required|exists:worker_profiles,id',
            'status' => 'nullable|in:pending,accepted,process,success,rejected', // Allow filtering by status
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Start building the query
        $query = JobRequests::where('worker_profile_id', $request->worker_profile_id);

        // Apply status filter if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Retrieve job requests with related data
        $jobRequests = $query->with(['user', 'workerProfile', 'job', 'files'])->get();

        return response()->json([
            'message' => 'Worker job requests retrieved successfully.',
            'data' => $jobRequests,
        ], 200);
    }

    public function jobRequestDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_request_id' => 'required|exists:job_requests,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $jobRequestId = $request->job_request_id;

        $jobRequest = JobRequests::with(['user', 'workerProfile', 'job', 'files'])->find($jobRequestId);

        if (!$jobRequest) {
            return response()->json(['message' => 'Job request not found.'], 404);
        }

        return response()->json([
            'message' => 'Job request retrieved successfully.',
            'data' => $jobRequest,
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_request_id' => 'required|exists:job_requests,id',
            'status' => 'required|in:pending,accepted,process,success,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $jobRequest = JobRequests::find($request->job_request_id);

        if (!$jobRequest) {
            return response()->json(['message' => 'Job request not found.'], 404);
        }

        $jobRequest->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Job request updated successfully.',
            'data' => $jobRequest,
        ], 200);
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_request_id' => 'required|exists:job_requests,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $jobRequest = JobRequests::find($request->job_request_id);

        if (!$jobRequest) {
            return response()->json(['message' => 'Job request not found.'], 404);
        }

        // Delete associated files
        foreach ($jobRequest->files as $file) {
            GlobalFunction::deleteFile($file->file_path);
            $file->delete();
        }

        $jobRequest->delete();

        return response()->json(['message' => 'Job request deleted successfully.'], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AppData;
use App\Models\Comment;
use App\Models\Constants;
use App\Models\FollowingList;
use App\Models\GlobalFunction;
use App\Models\Images;
use App\Models\Interest;
use App\Models\Invites;
use App\Models\Like;
use App\Models\LikedProfile;
use App\Models\LiveApplications;
use App\Models\LiveHistory;
use App\Models\Myfunction;
use App\Models\Packages;
use App\Models\Post;
use App\Models\PostContent;
use App\Models\PromotionPackage;
use App\Models\PromotionPackageTransactions;
use App\Models\RedeemRequest;
use App\Models\Report;
use App\Models\RevenueSharingRule;
use App\Models\Story;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserPackages;
use App\Models\UserPackageTransactions;
use App\Models\UserPromotionPackage;
use App\Models\Users;
use App\Models\VerifyRequest;
use App\Models\WalletTags;
use App\Models\WalletTransactions;
use Faker\Core\Number;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isEmpty;

class UsersController extends Controller
{
    public $envImage;
    public function __construct()
    {
        $this->envImage = env('image');
    }
    function addCoinsToUserWalletFromAdmin(Request $request)
    {
        // $result = Users::where('id', $request->id)->increment('wallet', $request->coins);
        $result = GlobalFunction::addCoinsToWallet($request->id, $request->coins, 1, 10);
        if ($result->getStatusCode() === 200) {
            $response['success'] = 1;
        } else {
            $response['success'] = 0;
        }
        echo json_encode($response);
    }

    function logOutUser(Request $request)
    {
        $rules = [
            'user_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->user_id)->first();

        if ($user == null) {
            return json_encode([
                'status' => false,
                'message' => 'user not found!',
            ]);
        }

        $user->device_token = null;
        $user->save();

        return response()->json(['status' => true, 'message' => 'User logged out successfully !']);
    }

    function fetchUsersByCordinates(Request $request)
    {
        $rules = [
            'lat' => 'required',
            'long' => 'required',
            'km' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $users = Users::with('images')->where('is_block', 0)->where('is_fake', 0)->where('show_on_map', 1)->where('anonymous', 0)->get();

        $usersData = [];
        foreach ($users as $user) {

            $distance = Myfunction::point2point_distance($request->lat, $request->long, $user->lattitude, $user->longitude, "K", $request->km);
            if ($distance) {
                array_push($usersData, $user);
            }
        }
        return response()->json(['status' => true, 'message' => 'Data fetched successfully !', 'data' => $usersData]);
    }

    function addUserImage(Request $req)
    {
        $img = new Images();
        $file = $req->file('image');
        $path = GlobalFunction::saveFileAndGivePath($file);
        $img->image = $path;
        $img->user_id = $req->id;
        $img->save();

        return json_encode([
            'status' => true,
            'message' => 'Image Added successfully!',
        ]);
    }

    function deleteUserImage($imgId)
    {
        $img = Images::find($imgId);

        $imgCount = Images::where('user_id', $img->user_id)->count();
        if ($imgCount == 1) {
            return json_encode([
                'status' => false,
                'message' => 'Minimum one image is required !',
            ]);
        }

        // unlink(storage_path('app/public/' . $img->image));
        GlobalFunction::deleteFile($img->image);
        $img->delete();
        return json_encode([
            'status' => true,
            'message' => 'Image Deleted successfully!',
        ]);
    }

    function updateUser(Request $req)
    {

        $result = Users::where('id', $req->id)->update([
            "fullname" => $req->fullname,
            "age" => $req->age,
            "password" => $req->password,
            "bio" => $req->bio,
            "about" => $req->about,
            "instagram" => $req->instagram,
            "youtube" => $req->youtube,
            "facebook" => $req->facebook,
            "live" => $req->live,
            "role" => $req->role
        ]);

        if ($req->has('user_package')) {
            $userId = $req->id;
            $packageId = $req->user_package;

            if ($packageId !== null) {
                try {
                    // Attempt to find the package
                    $package = Packages::findOrFail($packageId);

                    // Update or create user package
                    $userPackage = UserPackages::updateOrCreate(
                        ['user_id' => $userId],
                        [
                            'package_id' => $package->id,
                            'start_date' => now(),
                            'end_date' => now()->addDays($package->duration_days)
                        ]
                    );

                    Log::info("Update user package [UserId]: $userId By Admin [PackageName]: {$package->name} [EndDate] {$userPackage->end_date}");
                } catch (\Exception $e) {
                    // Log the error if the package is not found or another error occurs
                    Log::error("Failed to update user package for user $userId: " . $e->getMessage());
                }
            } else {
                // Remove user package
                try {
                    $userPackage = UserPackages::where('user_id', $userId)->first();
                    if ($userPackage) {
                        $userPackage->delete();
                        Log::info("Remove user package [UserId]: $userId By Admin");
                    }
                } catch (\Exception $e) {
                    // Log the error if deletion fails
                    Log::error("Failed to remove user package for user $userId: " . $e->getMessage());
                }
            }
        }

        if ($req->has('promotion_package')) {
            $userId = $req->id;
            $promotionPackageId = $req->promotion_package;

            if ($promotionPackageId !== null) {
                try {
                    // Attempt to find the package
                    $promotionPackage = PromotionPackage::findOrFail($promotionPackageId);

                    // Update or create user package
                    $userPromotionPackage = UserPromotionPackage::updateOrCreate(
                        ['user_id' => $userId],
                        [
                            'promotion_package_id' => $promotionPackage->id,
                            'start_date' => now(),
                            'end_date' => now()->addDays($promotionPackage->duration_days)
                        ]
                    );

                    Log::info("Update promotion package [UserId]: $userId By Admin [PackageName]: {$promotionPackage->name} [EndDate] {$promotionPackage->end_date}");
                } catch (\Exception $e) {
                    // Log the error if the package is not found or another error occurs
                    Log::error("Failed to update promotion package for user $userId: " . $e->getMessage());
                }
            } else {
                // Remove user package
                try {
                    $userPromotionPackage = UserPromotionPackage::where('user_id', $userId)->first();
                    if ($userPromotionPackage) {
                        $userPromotionPackage->delete();
                        Log::info("Remove promotion package [UserId]: $userId By Admin");
                    }
                } catch (\Exception $e) {
                    // Log the error if deletion fails
                    Log::error("Failed to remove promotion package for user $userId: " . $e->getMessage());
                }
            }
        }

        return json_encode([
            'status' => true,
            'message' => 'data updates successfully!',
        ]);
    }

    // updateUserPackage
    function updateUserPackage(Request $req)
    {
        if ($req->has('user_package')) {
            $userId = $req->id;
            $packageId = $req->user_package;

            if ($packageId !== null) {
                try {
                    // Attempt to find the package
                    $package = Packages::findOrFail($packageId);

                    // Check if the user has an existing active package
                    $existingActivePackage = UserPackageTransactions::where('user_id', $userId)
                        ->where('status', 'active')
                        ->first();

                    if ($existingActivePackage) {
                        // Update the existing active package to "remove" and "inactive"
                        $existingActivePackage->update([
                            'action' => 'remove',
                            'status' => 'inactive',
                            'end_date' => now(),
                        ]);
                    }

                    // Update or create user package
                    $userPackage = UserPackages::updateOrCreate(
                        ['user_id' => $userId],
                        [
                            'package_id' => $package->id,
                            'start_date' => now(),
                            'end_date' => now()->addDays($package->duration_days),
                        ]
                    );

                    // Log the transaction for assigning a new package
                    UserPackageTransactions::create([
                        'user_id' => $userId,
                        'package_id' => $package->id,
                        'action' => 'assign',
                        'start_date' => now(),
                        'end_date' => now()->addDays($package->duration_days),
                        'status' => 'active',
                        'created_by_admin_id' => Session::get('user_id'), // Admin ID
                    ]);

                    Log::info("Update user package [UserId]: $userId By Admin [PackageName]: {$package->name} [EndDate] {$userPackage->end_date}");
                } catch (\Exception $e) {
                    Log::error("Failed to update user package for user $userId: " . $e->getMessage());
                }
            } else {
                // Remove user package
                try {
                    $userPackage = UserPackages::where('user_id', $userId)->first();
                    if ($userPackage) {
                        // Update the existing active package to "remove" and "inactive"
                        $existingActivePackage = UserPackageTransactions::where('user_id', $userId)
                            ->where('status', 'active')
                            ->first();

                        if ($existingActivePackage) {
                            $existingActivePackage->update([
                                'action' => 'remove',
                                'status' => 'inactive',
                                'end_date' => now(),
                            ]);
                        }

                        $userPackage->delete();
                        Log::info("Remove user package [UserId]: $userId By Admin");
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to remove user package for user $userId: " . $e->getMessage());
                }
            }
        }

        return json_encode([
            'status' => true,
            'message' => 'Data updated successfully!',
        ]);
    }

    // updatePromotionPackage
    function updatePromotionPackage(Request $req)
    {

        if ($req->has('promotion_package')) {
            $userId = $req->id;
            $promotionPackageId = $req->promotion_package;

            if ($promotionPackageId !== null) {
                try {
                    // Attempt to find the package
                    $promotionPackage = PromotionPackage::findOrFail($promotionPackageId);

                    // Check if the user has an existing active package
                    $existingActivePackage = PromotionPackageTransactions::where('user_id', $userId)
                        ->where('status', 'active')
                        ->first();

                    if ($existingActivePackage) {
                        // Update the existing active package to "remove" and "inactive"
                        $existingActivePackage->update([
                            'action' => 'remove',
                            'status' => 'inactive',
                            'end_date' => now(),
                        ]);
                    }

                    // Update or create user package
                    $userPromotionPackage = UserPromotionPackage::updateOrCreate(
                        ['user_id' => $userId],
                        [
                            'promotion_package_id' => $promotionPackage->id,
                            'start_date' => now(),
                            'end_date' => now()->addDays($promotionPackage->duration_days)
                        ]
                    );

                    // Log the transaction for assigning a new package
                    PromotionPackageTransactions::create([
                        'user_id' => $userId,
                        'promotion_package_id' => $promotionPackage->id,
                        'action' => 'assign',
                        'start_date' => now(),
                        'end_date' => now()->addDays($promotionPackage->duration_days),
                        'status' => 'active',
                        'created_by_admin_id' => Session::get('user_id'), // Admin ID
                    ]);

                    Log::info("Update promotion package [UserId]: $userId By Admin [PackageName]: {$promotionPackage->name} [EndDate] {$promotionPackage->end_date}");
                } catch (\Exception $e) {
                    // Log the error if the package is not found or another error occurs
                    Log::error("Failed to update promotion package for user $userId: " . $e->getMessage());
                }
            } else {
                // Remove user package
                try {
                    $userPromotionPackage = UserPromotionPackage::where('user_id', $userId)->first();
                    if ($userPromotionPackage) {
                        // Update the existing active package to "remove" and "inactive"
                        $existingActivePackage = PromotionPackageTransactions::where('user_id', $userId)
                            ->where('status', 'active')
                            ->first();

                        if ($existingActivePackage) {
                            $existingActivePackage->update([
                                'action' => 'remove',
                                'status' => 'inactive',
                                'end_date' => now(),
                            ]);
                        }

                        $userPromotionPackage->delete();
                        Log::info("Remove promotion package [UserId]: $userId By Admin");
                    }
                } catch (\Exception $e) {
                    // Log the error if deletion fails
                    Log::error("Failed to remove promotion package for user $userId: " . $e->getMessage());
                }
            }
        }

        return json_encode([
            'status' => true,
            'message' => 'data updates successfully!',
        ]);
    }

    function removeUserPackage(Request $req)
    {
        try {
            $userId = $req->user_id;

            // Find the active package of the user
            $userPackage = UserPackages::where('user_id', $userId)->first();

            if ($userPackage) {
                // Update the existing active package to "remove" and "inactive"
                $existingActivePackage = UserPackageTransactions::where('user_id', $userId)
                    ->where('status', 'active')
                    ->first();

                if ($existingActivePackage) {
                    $existingActivePackage->update([
                        'action' => 'remove',
                        'status' => 'inactive',
                        'end_date' => now(),
                    ]);
                }

                // Delete the package from the user_packages table
                $userPackage->delete();

                Log::info("Remove user package [UserId]: $userId");
            } else {
                Log::warning("No active package found for user [UserId]: $userId");
            }
        } catch (\Exception $e) {
            Log::error("Failed to remove user package for user $userId: " . $e->getMessage());
        }

        return json_encode([
            'status' => true,
            'message' => 'Package removed successfully!',
        ]);
    }
    function removePromotionPackage(Request $req)
    {
        try {
            $userId = $req->user_id;

            // Find the active package of the user
            $userPackage = UserPromotionPackage::where('user_id', $userId)->first();

            if ($userPackage) {
                // Update the existing active package to "remove" and "inactive"
                $existingActivePackage = PromotionPackageTransactions::where('user_id', $userId)
                    ->where('status', 'active')
                    ->first();

                if ($existingActivePackage) {
                    $existingActivePackage->update([
                        'action' => 'remove',
                        'status' => 'inactive',
                        'end_date' => now(),
                    ]);
                }

                // Delete the package from the user_packages table
                $userPackage->delete();

                Log::info("Remove promotion package [UserId]: $userId");
            } else {
                Log::warning("No active promotion package found for user [UserId]: $userId");
            }
        } catch (\Exception $e) {
            Log::error("Failed to remove promotion package for user $userId: " . $e->getMessage());
        }

        return json_encode([
            'status' => true,
            'message' => 'Promotion package removed successfully!',
        ]);
    }

    function test(Request $req)
    {

        $user = Users::with('liveApplications')->first();

        $intrestIds = Interest::inRandomOrder()->limit(4)->pluck('id');

        return json_encode(['data' => $intrestIds]);
    }

    function addFakeUserFromAdmin(Request $request)
    {
        $user = new Users();
        $user->identity = Myfunction::generateFakeUserIdentity();
        $user->fullname = $request->fullname;
        $user->youtube = $request->youtube;
        $user->facebook = $request->facebook;
        $user->instagram = $request->instagram;
        $user->age = $request->age;
        $user->live = $request->live;
        $user->about = $request->about;
        $user->bio = $request->bio;
        $user->password = $request->password;
        $user->gender = $request->gender;
        $user->is_verified = 2;
        $user->can_go_live = 2;
        $user->is_fake = 1;

        // Interests
        $interestIds = Interest::inRandomOrder()->limit(4)->pluck('id')->toArray();
        $user->interests = implode(',', $interestIds);

        $user->save();

        if ($request->hasFile('image')) {
            $files = $request->file('image');
            for ($i = 0; $i < count($files); $i++) {
                $image = new Images();
                $image->user_id = $user->id;
                $path = GlobalFunction::saveFileAndGivePath($files[$i]);
                $image->image = $path;
                $image->save();
            }
        }

        return response()->json(['status' => true, 'message' => "Fake user added successfully !"]);
    }

    public function getExplorePageProfileList(Request $request)
    {
        $rules = [
            'user_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->user_id)->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found!',
            ]);
        }

        $genderPreference = $user->gender_preferred;
        $ageMin = $user->age_preferred_min;
        $ageMax = $user->age_preferred_max;
        $blockedUsers = array_merge(explode(',', $user->blocked_users), [$user->id]);
        $likedUsers = LikedProfile::where('my_user_id', $request->user_id)->pluck('user_id')->toArray();

        $profilesQuery = Users::with(['workerProfile', 'shopUser', 'package.package', 'promotionPackage.promotionPackage', 'images'])
            ->withCount('likedProfiles')
            ->has('images')
            ->whereNotIn('id', $blockedUsers)
            ->where('is_block', 0)
            ->when($genderPreference != 3, function ($query) use ($genderPreference) {
                $query->where('gender', $genderPreference == 1 ? 1 : 2);
            })
            ->when($ageMin && $ageMax, function ($query) use ($ageMin, $ageMax) {
                $query->whereBetween('age', [$ageMin, $ageMax]);
            })
            ->inRandomOrder()
            ->limit(15);

        $profiles = $profilesQuery->get()->each(function ($profile) use ($likedUsers) {
            $profile->is_like = in_array($profile->id, $likedUsers);
            if ($profile->workerProfile === null) {
                $profile->workerProfile = false;
            } else {
                $profile->workerProfile = true;
            }
            if ($profile->shopUser === null) {
                $profile->shopUser = false;
            } else {
                $profile->shopUser = true;
            }
            if ($profile->promotionPackage === null) {
                $profile->promotionPackage = false;
            } else {
                $profile->promotionPackage = true;
            }
            if ($profile->package === null) {
                $profile->userPackage = false;
            } else {
                $profile->userPackage = true;
            }
            return $profile;
        });

        return response()->json([
            'status' => true,
            'message' => 'Data found successfully!',
            'data' => $profiles,
        ]);
    }


    function getRandomProfile(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'gender' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->user_id)->first();
        if ($user == null) {
            return response()->json([
                'status' => false,
                'message' => 'User not found!',
            ]);
        }

        $blocked_users = explode(',', $user->blocked_users);
        array_push($blocked_users, $user->id);

        if ($request->gender == 3) {
            $randomUser = Users::with('images')->has('images')->whereNotIn('id', $blocked_users)->where('is_block', 0)->inRandomOrder()->first();
        } else {
            $randomUser = Users::with('images')->has('images')->whereNotIn('id', $blocked_users)->where('is_block', 0)->where('gender', $request->gender)->inRandomOrder()->first();
        }

        if ($randomUser == null) {
            return response()->json([
                'status' => false,
                'message' => 'User not found!',
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'data found successfully!',
            'data' => $randomUser,
        ]);
    }

    function updateUserBlockList(Request $request)
    {
        $rules = [
            'user_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->user_id)->first();
        if ($user == null) {
            return response()->json(['status' => false, 'message' => "User doesn't exists !"]);
        }

        $user->blocked_users = $request->blocked_users;
        $user->save();

        $data = Users::with('images')->where('id', $request->user_id)->first();

        return response()->json(['status' => true, 'message' => "Blocklist updated successfully !", 'data' => $data]);
    }

    function deleteMyAccount(Request $request)
    {
        $rules = [
            'user_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->user_id)->first();
        if ($user == null) {
            return json_encode([
                'status' => false,
                'message' => 'user not found!',
            ]);
        }

        Images::where('user_id', $user->id)->delete();

        $likes = Like::where('user_id', $user->id)->get();
        foreach ($likes as $like) {
            $postLikeCount = Post::where('id', $like->post_id)->first();
            $postLikeCount->likes_count -= 1;
            $postLikeCount->save();
        }
        $comments = Comment::where('user_id', $user->id)->get();
        foreach ($comments as $comment) {
            $postCommentCount = Post::where('id', $comment->post_id)->first();
            $postCommentCount->comments_count -= 1;
            $postCommentCount->save();
        }


        $posts = Post::where('user_id', $user->id)->get();
        foreach ($posts as $post) {
            $postContents = PostContent::where('post_id', $post->id)->get();
            foreach ($postContents as $postContent) {
                GlobalFunction::deleteFile($postContent->content);
                GlobalFunction::deleteFile($postContent->thumbnail);
                $postContent->delete();
            }
            UserNotification::where('post_id', $post->id)->delete();
            $post->delete();
        }

        $stories = Story::where('user_id', $user->id)->get();
        foreach ($stories as $story) {
            GlobalFunction::deleteFile($story->content);
            $story->delete();
        }


        UserNotification::where('user_id', $user->id)->delete();
        LiveApplications::where('user_id', $user->id)->delete();
        LiveHistory::where('user_id', $user->id)->delete();
        RedeemRequest::where('user_id', $user->id)->delete();
        VerifyRequest::where('user_id', $user->id)->delete();
        Report::where('user_id', $user->id)->delete();
        UserNotification::where('my_user_id', $user->id)->delete();
        UserNotification::where('my_user_id', $user->id)->orWhere('user_id', $user->id)->delete();
        $user->delete();

        return response()->json(['status' => true, 'message' => "Account Deleted Successfully !"]);
    }

    function rejectVerificationRequest(Request $request)
    {
        $verifyRequest = VerifyRequest::where('id', $request->verification_id)->first();
        $verifyRequest->user->is_verified = 0;
        $verifyRequest->user->save();

        GlobalFunction::deleteFile($verifyRequest->document);
        GlobalFunction::deleteFile($verifyRequest->selfie);

        $verifyRequest->delete();

        return response()->json([
            'status' => true,
            'message' => 'Reject Verification Request',
        ]);
    }

    function approveVerificationRequest(Request $request)
    {
        $verifyRequest = VerifyRequest::where('id', $request->verification_id)->first();
        $verifyRequest->user->is_verified = 2;
        $verifyRequest->user->save();

        GlobalFunction::deleteFile($verifyRequest->document);
        GlobalFunction::deleteFile($verifyRequest->selfie);

        $verifyRequest->delete();

        return response()->json([
            'status' => true,
            'message' => 'Approve Verification Request',
        ]);
    }

    public function fetchverificationRequests(Request $request)
    {
        $totalData = VerifyRequest::count();
        $rows = VerifyRequest::orderBy('id', 'DESC')->get();

        $result = $rows;

        $columns = array(
            0 => 'id'
        );

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $totalData = VerifyRequest::count();
        $totalFiltered = $totalData;
        if (empty($request->input('search.value'))) {
            $result = VerifyRequest::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  VerifyRequest::with('user')
                ->whereHas('user', function ($query) use ($search) {
                    $query->Where('fullname', 'LIKE', "%{$search}%")
                        ->orWhere('identity', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = VerifyRequest::with('user')
                ->whereHas('user', function ($query) use ($search) {
                    $query->Where('fullname', 'LIKE', "%{$search}%")
                        ->orWhere('identity', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $imgUrl = "http://placehold.jp/150x150.png"; // Default placeholder image URL

            if ($item->user->images->isNotEmpty() && $item->user->images[0]->image != null) {
                $imgUrl = asset('storage/' . $item->user->images[0]->image);
            }

            $image = '<img src="' . $imgUrl . '" width="50" height="50">';

            $selfieUrl = "public/storage/" . $item->selfie;
            $selfie = '<img style="cursor: pointer;" class="img-preview" rel="' . $selfieUrl . '" src="' . $selfieUrl . '" width="50" height="50">';

            $docUrl = "public/storage/" . ($item->document);
            $document = '<img style="cursor: pointer;" class="img-preview" rel="' . $docUrl . '" src="' . $docUrl . '" width="50" height="50">';

            $approve = '<a href=""class=" btn btn-success text-white approve ml-2" rel=' . $item->id . ' >' . __("Approve") . '</a>';
            $reject = '<a href=""class=" btn btn-danger text-white reject ml-2" rel=' . $item->id . ' >' . __("Reject") . '</a>';

            $action = '<span class="float-end d-flex">' . $approve . $reject . ' </span>';

            $data[] = array(
                $image,
                $selfie,
                $document,
                $item->document_type,
                $item->fullname,
                $item->user->identity,
                $action
            );
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data"            => $data
        );
        echo json_encode($json_data);
        exit();
    }

    function verificationrequests()
    {
        return view('verificationrequests');
    }

    function applyForVerification(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'document' => 'required',
            'document_type' => 'required',
            'selfie' => 'required',
            'fullname' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->user_id)->first();
        if ($user == null) {
            return json_encode([
                'status' => false,
                'message' => 'user not found!',
            ]);
        }

        if ($user->is_verified == 1) {
            return response()->json([
                'status' => false,
                'message' => 'The request has been submitted already!',
            ]);
        }
        if ($user->is_verified == 2) {
            return response()->json([
                'status' => false,
                'message' => 'This user is already verified !',
            ]);
        }

        $verifyReq = new VerifyRequest();
        $verifyReq->user_id = $request->user_id;
        $verifyReq->document_type = $request->document_type;
        $verifyReq->fullname = $request->fullname;
        $verifyReq->status = 0;

        $verifyReq->document = GlobalFunction::saveFileAndGivePath($request->document);
        $verifyReq->selfie = GlobalFunction::saveFileAndGivePath($request->selfie);

        $verifyReq->save();

        $user->is_verified = 1;
        $user->save();

        $user['images'] = Images::where('user_id', $request->user_id)->get();

        return response()->json([
            'status' => true,
            'message' => "Verification request submitted successfully !",
            'data' => $user
        ]);
    }

    public function updateLikedProfile(Request $request)
    {
        $rules = [
            'my_user_id' => 'required',
            'user_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        $user = Users::where('id', $request->user_id)->first();
        $my_user = Users::where('id', $request->my_user_id)->first();

        if (!$user || !$my_user) {
            return response()->json([
                'status' => false,
                'message' => !$user ? 'User not found!' : 'Data user not found!',
            ]);
        }

        $fetchLikedProfile = LikedProfile::where('my_user_id', $request->my_user_id)
            ->where('user_id', $request->user_id)
            ->first();

        $notificationExists = UserNotification::where('user_id', $request->user_id)
            ->where('my_user_id', $request->my_user_id)
            ->where('type', Constants::notificationTypeLikeProfile)
            ->first();

        if ($fetchLikedProfile) {
            $fetchLikedProfile->delete();
            $notificationExists?->delete();

            return response()->json(['status' => true, 'message' => 'Profile disliked!']);
        } else {
            $likedProfile = new LikedProfile();
            $likedProfile->my_user_id = (int) $request->my_user_id;
            $likedProfile->user_id = (int) $request->user_id;
            $likedProfile->save();

            if (!$notificationExists) {
                $userNotification = new UserNotification();
                $userNotification->user_id = (int) $user->id;
                $userNotification->my_user_id = (int) $my_user->id;
                $userNotification->type = Constants::notificationTypeLikeProfile;
                $userNotification->save();

                if ($user->id != $my_user->id && $user->is_notification) {
                    $message = "{$my_user->fullname} has liked your profile, you should check their profile!";
                    Myfunction::sendPushToUser(env('APP_NAME'), $message, $user->device_token);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Update Liked Profile Successfully!',
                'data' => $likedProfile
            ]);
        }
    }

    function fetchBlockedProfiles(Request $request)
    {

        $rules = [
            'user_id' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->user_id)->first();

        if ($user == null) {
            return json_encode([
                'status' => false,
                'message' => 'user not found!',
            ]);
        }

        $array = explode(',', $user->blocked_users);
        $data = Users::whereIn('id', $array)->where('is_block', 0)->with('images')->has('images')->get();
        $data = $data->reverse()->values();

        return json_encode([
            'status' => true,
            'message' => 'blocked profiles fetched successfully!',
            'data' => $data
        ]);
    }

    function fetchLikedProfiles(Request $request)
    {
        $rules = [
            'user_id' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->user_id)->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'user not found!',
            ]);
        }

        $likedProfiles = LikedProfile::where('my_user_id', $request->user_id)
            ->with('user')
            ->whereRelation('user', 'is_block', 0)
            ->has('user.images')
            ->with('user.images')
            ->orderBy('id', 'DESC')
            ->get()
            ->pluck('user');

        foreach ($likedProfiles as $likedProfile) {
            $likedProfile->is_like = true;
        }

        return response()->json([
            'status' => true,
            'message' => 'profiles fetched successfully!',
            'data' => $likedProfiles
        ]);
    }

    function fetchSavedProfiles(Request $request)
    {

        $rules = [
            'user_id' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->user_id)->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'user not found!',
            ]);
        }

        $array = explode(',', $user->savedprofile);
        $data =  Users::whereIn('id', $array)->where('is_block', 0)->has('images')->with('images')->get();
        $data = $data->reverse()->values();

        return response()->json([
            'status' => true,
            'message' => 'Fetched Saved Profiles Successfully!',
            'data' => $data
        ]);
    }

    function allowLiveToUser(Request $request)
    {
        $user = Users::where('id', $request->user_id)->first();

        if ($user) {
            $user->can_go_live = 2;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => "This user is allowed to go live.",
                'data' => $user,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ]);
        }
    }

    function restrictLiveToUser(Request $request)
    {
        $user = Users::where('id', $request->user_id)->first();

        if ($user) {
            $user->can_go_live = 0;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => "Restrict Live Access to User.",
                'data' => $user,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ]);
        }
    }

    function increaseStreamCountOfUser(Request $request)
    {
        $rules = [
            'user_id' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->user_id)->first();

        if ($user == null) {
            return json_encode([
                'status' => false,
                'message' => 'user not found!',
            ]);
        }

        $user->total_streams += 1;
        $result = $user->save();

        if ($result) {
            return json_encode([
                'status' => true,
                'message' => 'Stream count increased successfully',
                'total_streams' => $user->total_streams
            ]);
        } else {
            return json_encode([
                'status' => false,
                'message' => 'something went wrong!',

            ]);
        }
    }

    function minusCoinsFromWallet(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'amount' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        // $user = Users::where('id', $request->user_id)->first();

        // if ($user == null) {
        //     return json_encode([
        //         'status' => false,
        //         'message' => 'user not found!',
        //     ]);
        // }

        // if ($user->wallet < $request->amount) {
        //     return json_encode([
        //         'status' => false,
        //         'message' => 'No enough coins in the wallet!',
        //         'wallet' => $user->wallet,
        //     ]);
        // }

        // $user->wallet -= $request->amount;
        $result = GlobalFunction::minusCoinsFromWallet($request->user_id, $request->amount, 2);

        if ($result->getStatusCode() !== 200) {
            return response()->json([
                'status' => false,
                'message' => $result->getData()->message,
            ], $result->getStatusCode());
        } else {
            return response()->json([
                'status' => true,
                'message' => 'coins deducted from wallet successfully',
                'wallet' => $result->getData()->user->wallet,
                'total_collected' => $result->getData()->user->total_collected,
                'transaction' => $result->getData()->transaction,
            ]);
        }
    }

    function addCoinsToWallet(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'amount' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->user_id)->first();

        if ($user == null) {
            return json_encode([
                'status' => false,
                'message' => 'user not found!',
            ]);
        }

        $user->wallet  += $request->amount;
        $user->total_collected += $request->amount;
        $result = $user->save();

        if ($result) {
            return json_encode([
                'status' => true,
                'message' => 'coins added to wallet successfully',
                'wallet' => $user->wallet,
                'total_collected' => $user->total_collected,
            ]);
        } else {
            return json_encode([
                'status' => false,
                'message' => 'something went wrong!',

            ]);
        }
    }

    function updateLiveStatus(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'state' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->user_id)->first();
        $user->is_live_now = $request->state;
        $user->save();

        $data = Users::with('images')->has('images')->where('id', $request->user_id)->first();

        return json_encode([
            'status' => true,
            'message' => 'is_live_now state updated successfully',
            'data' => $data
        ]);
    }

    function onOffVideoCalls(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'state' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->user_id)->first();
        $user->is_video_call = $request->state;
        $user->save();

        $data = Users::with('images')->has('images')->where('id', $request->user_id)->first();

        return json_encode([
            'status' => true,
            'message' => 'is_video_call state updated successfully',
            'data' => $data
        ]);
    }

    function onOffAnonymous(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'state' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->user_id)->first();
        $user->anonymous = $request->state;
        $user->save();

        $data = Users::with('images')->has('images')->where('id', $request->user_id)->first();

        return json_encode([
            'status' => true,
            'message' => 'anonymous state updated successfully',
            'data' => $data
        ]);
    }

    function onOffShowMeOnMap(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'state' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->user_id)->first();
        $user->show_on_map = $request->state;
        $user->save();

        $data = Users::with('images')->has('images')->where('id', $request->user_id)->first();

        return json_encode([
            'status' => true,
            'message' => 'show_on_map state updated successfully',
            'data' => $data
        ]);
    }

    function onOffNotification(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'state' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->user_id)->first();
        $user->is_notification = $request->state;
        $user->save();

        $data = Users::with('images')->has('images')->where('id', $request->user_id)->first();

        return json_encode([
            'status' => true,
            'message' => 'notification state updated successfully',
            'data' => $data
        ]);
    }

    // แก้ไข function fetchAllUsers
    function fetchAllUsers(Request $request)
    {
        $totalData = 0;
        $query = Users::with(['inviter']);

        // กรอง Fake Users ออกเสมอในฟังก์ชันนี้ (เพราะมี Tab แยก)
        // $query->where('is_fake', 0);

        // ✅ กรองตาม Role จาก Dynamic Tab
        if ($request->has('role') && $request->role != 'all') {
            // dd($request->role);
            $query->where('role', $request->role);
        }

        // นับจำนวนข้อมูลทั้งหมด
        $totalData = $query->count();

        // ค้นหา (Search)
        $search = $request->input('search.value');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'LIKE', "%{$search}%")
                    ->orWhere('identity', 'LIKE', "%{$search}%");
            });
        }

        // นับจำนวนข้อมูลหลังการค้นหา
        $totalFiltered = $query->count();

        // Pagination และ Ordering
        $columns = [
            0 => 'id',
            1 => 'fullname'
        ];
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $rows = $query->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();
        $result = $rows;
        $data = array();

        foreach ($result as $item) {
            $inviterName = $item->inviter->fullname ?? '—';
            
            [$promotionPackageName, $endPromotionDate] = $this->getPromotionPackageDetails($item);

            if ($item->is_block == 0) {
                $block  =  '<a class="btn btn-danger text-white block" rel=' . $item->id . '>' . __('app.Block') . '</a>';
            } else {
                $block  =  '<a class="btn btn-success text-white unblock" rel=' . $item->id . '>' . __('app.Unblock') . '</a>';
            }

            if ($item->gender == 1) {
                $gender = ' <span class="badge bg-dark text-white">' . __('app.Male') . '</span>';
            } else {
                $gender = ' <span class="badge bg-dark text-white">' . __('app.Female') . '</span>';
            }

            if (count($item->images) > 0) {
                $image = '<img src="' . $this->envImage . $item->images[0]->image . '" width="50" height="50">';
            } else {
                $image = '<img src="http://placehold.jp/150x150.png" width="50" height="50">';
            }

            if ($item->can_go_live == 2) {
                $liveEligible = ' <span class="badge bg-success text-white">Yes</span>';
            } else {
                $liveEligible = ' <span class="badge bg-danger text-white">No</span>';
            }

            if ($promotionPackageName === "No Promotion Package") {
                $identityHtml = $item->identity;
            } else {
                $identityHtml = $item->identity . '<br/> <span class="badge bg-info text-white" style="font-size: 10px;padding:5px;">' . $promotionPackageName . '</span>';
            }

            // Role Badge Logic
            $roleColor = 'secondary';
            if ($item->role == 'staff') $roleColor = 'info';
            elseif ($item->role == 'entertainer') $roleColor = 'warning';
            elseif ($item->role == 'customer') $roleColor = 'success';
            
            $roleHtml = '<span class="badge bg-'.$roleColor.' text-white">'.ucfirst($item->role ?? 'customer').'</span>';

            $action = '<a href="' . route('viewUserDetails', $item->id) . '" class="btn btn-primary text-white" rel=' . $item->id . '><i class="fas fa-eye"></i></a>';
            $addCoin = '<a href="" data-id="' . $item->id . '" class="addCoins"><i class="i-cl-3 fas fa-plus-circle primary font-20 pointer p-l-5 p-r-5 me-2"></i></a>';

            $fullname = '<a href="' . route('invitee', ['id' => $item->id]) . '">' . $item->fullname . '<i class="fas fa-user-friends"></i></a> <br/><span style="font-size: 10px;">Invite Code: ' . $item->invite_code . '</span><br/><span class="badge bg-info text-white" style="font-size: 10px;padding:5px;">' . __('app.inviter') . '</span> <span style="font-size: 10px;">' . $inviterName . '</span>';
            
            $data[] = array(
                $image,
                $identityHtml,
                $fullname,
                $addCoin . $item->wallet,
                $liveEligible,
                $item->age,
                $gender,
                $block,
                $roleHtml, 
                $action,
            );
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data"            => $data
        );
        echo json_encode($json_data);
        exit();
    }

    // แก้ไข function fetchFakeUsers
    function fetchFakeUsers(Request $request)
    {
        $totalData =  Users::where('is_fake', '=', 1)->count();
        $rows = Users::with(['inviter'])->where('is_fake', '=', 1)->orderBy('id', 'DESC')->get();

        $result = $rows;

        $columns = array(
            0 => 'id',
            1 => 'fullname'
        );
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $totalFiltered = $totalData;
        if (empty($request->input('search.value'))) {
            $result = Users::where('is_fake', '=', 1)
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Users::where(function ($query) use ($search) {
                $query->Where('fullname', 'LIKE', "%{$search}%")
                    ->orWhere('identity', 'LIKE', "%{$search}%");
            })
                ->where('is_fake', '=', 1)
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Users::where(function ($query) use ($search) {
                $query->Where('fullname', 'LIKE', "%{$search}%")
                    ->orWhere('identity', 'LIKE', "%{$search}%");
            })
                ->where('is_fake', '=', 1)
                ->orWhere('fullname', 'LIKE', "%{$search}%")
                ->count();
        }
        $data = array();
        foreach ($result as $item) {
            $inviterName = $item->inviter->fullname ?? '—';
            // ลบการเรียก getPackageDetails

            [$promotionPackageName, $endPromotionDate] = $this->getPromotionPackageDetails($item);
            if ($item->is_block == 0) {
                $block  =  '<a class=" btn btn-danger text-white block" rel=' . $item->id . ' >' . __('app.Block') . '</a>';
            } else {
                $block  =  '<a class=" btn btn-success  text-white unblock " rel=' . $item->id . ' >' . __('app.Unblock') . '</a>';
            }

            if ($item->gender == 1) {
                $gender = ' <span  class="badge bg-dark text-white  ">' . __('app.Male') . '</span>';
            } else {
                $gender = '  <span  class="badge bg-dark text-white  ">' . __('app.Female') . '</span>';
            }

            if (count($item->images) > 0) {
                $image = '<img src="' . $this->envImage . $item->images[0]->image . '" width="50" height="50">';
            } else {
                $image = '<img src="http://placehold.jp/150x150.png" width="50" height="50">';
            }

            if ($promotionPackageName === "No Promotion Package") {
                $fullname = $item->fullname;
            } else {
                $fullname = $item->fullname . '<br/> <span class="badge bg-info text-white" style="font-size: 10px;padding:5px;">' . $promotionPackageName . '</span>';
            }

            $action = '<a href="' . route('viewUserDetails', $item->id) . '"class=" btn btn-primary text-white " rel=' . $item->id . ' ><i class="fas fa-eye"></i></a>';

            $fullname = '<a href="' . route('invitee', ['id' => $item->id]) . '">' . $item->fullname . '<i class="fas fa-user-friends"></i></a> <br/><span style="font-size: 10px;">Invite Code: ' . $item->invite_code . '</span><br/><span class="badge bg-info text-white" style="font-size: 10px;padding:5px;">' . __('app.inviter') . '</span> <span style="font-size: 10px;">' . $inviterName . '</span>';

            // Role Badge
            $roleColor = 'secondary';
            if ($item->role == 'staff') $roleColor = 'info';
            elseif ($item->role == 'entertainer') $roleColor = 'warning';
            elseif ($item->role == 'customer') $roleColor = 'success';

            $roleHtml = '<span class="badge bg-' . $roleColor . ' text-white">' . ucfirst($item->role ?? 'customer') . '</span>';

            $data[] = array(
                $image,
                $fullname,
                $item->identity,
                $item->password,
                $item->age,
                $gender,
                $block,
                $roleHtml, // แสดง Role แทน PackageName
                // $endDate,  // เอา EndDate ออก
                $action,
            );
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data"            => $data
        );
        echo json_encode($json_data);
        exit();
    }

    private function getPackageDetails($user)
    {
        $packageName = 'No Package';
        $endDate = '-';

        if ($user->package && $user->package->package) {
            $packageName = $user->package->package->name;
            $endDate = $user->package->end_date
                ? \Carbon\Carbon::parse($user->package->end_date)->format('Y-m-d')
                : '-';
        }

        return [$packageName, $endDate];
    }
    private function getPromotionPackageDetails($user)
    {
        $promotionPackageName = 'No Promotion Package';
        $endPromotionDate = '-';

        if ($user->promotionPackage && $user->promotionPackage->promotionPackage) {
            $promotionPackageName = $user->promotionPackage->promotionPackage->name;
            $endPromotionDate = $user->promotionPackage->end_date
                ? \Carbon\Carbon::parse($user->promotionPackage->end_date)->format('Y-m-d')
                : '-';
        }

        return [$promotionPackageName, $endPromotionDate];
    }

    function generateUniqueUsername()
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $username = '';
        $length = 8;

        do {
            for ($i = 0; $i < $length; $i++) {
                $username .= $characters[rand(0, strlen($characters) - 1)];
            }

            $existingUser = Users::where('username', $username)->first();
        } while ($existingUser);

        return $username;
    }

    function addUserDetails(Request $req)
    {

        if ($req->has('password')) {
            $data = Users::where('identity', $req->identity)->where('password', $req->password)->first();
            if ($data == null) {
                return json_encode(['status' => false, 'message' => "Incorrect Identity and Password combination"]);
            }
        }

        $data = Users::where('identity', $req->identity)->first();

        if ($data == null) {
            // check invite_code in db
            $inviter = null;
            if ($req->filled('invite_code')) {
                $inviter = Users::where('invite_code', $req->invite_code)->first();

                if (!$inviter) {
                    return response()->json([
                        'status' => false,
                        'message' => "Invalid invite code"
                    ]);
                }
            }

            $user = new Users;
            $user->fullname = Myfunction::customReplace($req->fullname);
            $user->identity = $req->identity;
            $user->device_token = $req->device_token;
            $user->device_type = $req->device_type;
            $user->login_type = $req->login_type;
            $user->username = $this->generateUniqueUsername();

            // create invite code for new user
            $inviteCode = '';
            do {
                $inviteCode = strtoupper(Str::random(8));
            } while (Users::where('invite_code', $inviteCode)->exists());

            $user->invite_code = $inviteCode;

            $user->save();

            if ($inviter) {
                Invites::create([
                    'inviter_id' => $inviter->id,
                    'invitee_id' => $user->id,
                ]);
                // get invite incone config
                $inviteIncomeConfig = RevenueSharingRule::where('action_key', 'invite_income')->first();
                if ($inviteIncomeConfig) {
                    $inviteIncomeAmount = $inviteIncomeConfig->customer_percent ?? 0;
                    GlobalFunction::addCoinsToWallet($inviter->id, $inviteIncomeAmount, 1, 9);
                }
            }

            $data =  Users::with('images')->where('id', $user->id)->first();

            return response()->json([
                'status' => true,
                'message' => __('app.UserAddSuccessful'),
                'data' => $data
            ]);
        } else {
            Users::where('identity', $req->identity)->update([
                'device_token' => $req->device_token,
                'device_type' => $req->device_type,
                'login_type' => $req->login_type,

            ]);

            $data = Users::with('images')->where('id', $data['id'])->first();

            return response()->json(['status' => true, 'message' => __('app.UserAllReadyExists'), 'data' => $data]);
        }
    }

    function searchUsersForInterest(Request $req)
    {

        $rules = [
            'start' => 'required',
            'count' => 'required',
            'interest_id' => 'required',
        ];

        $validator = Validator::make($req->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $interestID = $req->interest_id;

        $result =  Users::with('images')
            ->Where('fullname', 'LIKE', "%{$req->keyword}%")
            ->whereRaw("find_in_set($interestID , interests)")
            ->has('images')
            ->where('is_block', 0)
            ->where('anonymous', 0)
            ->offset($req->start)
            ->limit($req->count)
            ->get();

        if (isEmpty($result)) {
            return response()->json([
                'status' => true,
                'message' => 'No data found',
                'data' => $result
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'data get successfully',
            'data' => $result
        ]);
    }

    function searchUsers(Request $req)
    {

        $rules = [
            'start' => 'required',
            'count' => 'required',
        ];

        $validator = Validator::make($req->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $result =  Users::with('images')
            ->Where('fullname', 'LIKE', "%{$req->keyword}%")
            ->Where('username', 'LIKE', "%{$req->keyword}%")
            ->has('images')
            ->where('is_block', 0)
            ->where('anonymous', 0)
            ->offset($req->start)
            ->limit($req->count)
            ->get();

        if (isEmpty($result)) {
            return response()->json([
                'status' => true,
                'message' => 'No data found',
                'data' => $result
            ]);
        }
        return response()->json([
            'status' => true,
            'message' => 'data get successfully',
            'data' => $result
        ]);
    }

    function updateProfile(Request $req)
    { 
        $user = Users::where('id', $req->user_id)->first();

        if (!$user) {
            return json_encode(['status' => false, 'message' => __('app.UserNotFound')]);
        }

        if ($req->deleteimagestitle != null) {
            foreach ($req->deleteimagestitle as $oneImageData) {
                // unlink(storage_path('app/public/' . $oneImageData));
                GlobalFunction::deleteFile($oneImageData);
            }
        }

        if ($req->has("deleteimageids")) {
            Images::whereIn('id', $req->deleteimageids)->delete();
        }

        if ($req->has("role")) {
            $user->role = $req->role;
        }

        if ($req->has("fullname")) {
            $user->fullname = Myfunction::customReplace($req->fullname);
        }
        if ($req->has("username")) {
            $existingUser = Users::where('username', $req->username)
                ->where('id', '!=', $req->user_id)
                ->first();
            if ($existingUser !== null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Username is already taken',
                ]);
            }
            $user->username = Myfunction::customReplace($req->username);
        }
        if ($req->has("gender")) {
            $user->gender = $req->gender;
        }
        if ($req->has('youtube')) {
            $user->youtube = $req->youtube;
        }
        if ($req->has("instagram")) {
            $user->instagram = $req->instagram;
        }
        if ($req->has("facebook")) {
            $user->facebook = $req->facebook;
        }
        if ($req->has("live")) {
            $user->live =  Myfunction::customReplace($req->live);
        }
        if ($req->has("bio")) {
            $user->bio = Myfunction::customReplace($req->bio);
        }
        if ($req->has("about")) {
            $user->about = Myfunction::customReplace($req->about);
        }
        if ($req->has("lattitude")) {
            $user->lattitude = $req->lattitude;
        }
        if ($req->has("longitude")) {
            $user->longitude = $req->longitude;
        }
        if ($req->has("age")) {
            $user->age = $req->age;
        }
        if ($req->has("interests")) {
            $user->interests = $req->interests;
        }
        if ($req->has("gender_preferred")) {
            $user->gender_preferred = $req->gender_preferred;
        }
        if ($req->has("age_preferred_min")) {
            $user->age_preferred_min = $req->age_preferred_min;
        }
        if ($req->has("age_preferred_max")) {
            $user->age_preferred_max = $req->age_preferred_max;
        }
        $user->save();

        if ($req->hasFile('image')) {
            $files = $req->file('image');
            for ($i = 0; $i < count($files); $i++) {
                $image = new Images();
                $image->user_id = $user->id;
                $path = GlobalFunction::saveFileAndGivePath($files[$i]);
                $image->image = $path;
                $image->save();
            }
        }

        $updatedUser = Users::where('id', $user->id)->with('images')->first();

        return response()->json(['status' => true, 'message' => __('app.Updatesuccessful'), 'data' => $updatedUser]);
    }

    function blockUser(Request $request)
    {
        $user = Users::where('id', $request->user_id)->first();

        if ($user) {
            $user->is_block = Constants::blocked;
            $user->save();

            Report::where('user_id', $request->user_id)->delete();

            return response()->json([
                'status' => true,
                'message' => 'This user has been blocked',
                'data' => $user,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ]);
        }
    }

    function unblockUser(Request $request)
    {
        $user = Users::where('id', $request->user_id)->first();

        if ($user) {
            $user->is_block = Constants::unblocked;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'This user has been blocked',
                'data' => $user,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ]);
        }
    }

    function viewUserDetails($id)
    {

        $data = Users::where('id', $id)->with(['images', 'package.package', 'promotionPackage.promotionPackage'])->first();
        $data['packages'] = DB::table('packages')->get();
        $data['promotionPackages'] = DB::table('promotion_packages')->get();
        return view('viewuser', ['data' => $data]);
    }

    function getProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::with(['images', 'stories'])->has('images')->where('id', $request->user_id)->first();
        $myUser = Users::with('images')->has('images')->where('id', $request->my_user_id)->first();
        if ($user == null || $myUser == null) {
            return response()->json([
                'status' => false,
                'message' =>  'User Not Found!',
            ]);
        }

        $followingStatus = FollowingList::whereRelation('user', 'is_block', 0)->where('user_id', $request->my_user_id)->where('my_user_id', $request->user_id)->first();
        $followingStatus2 = FollowingList::whereRelation('user', 'is_block', 0)->where('my_user_id', $request->my_user_id)->where('user_id', $request->user_id)->first();

        // koi ek bija ne follow nathi kartu to 0
        if ($followingStatus == null && $followingStatus2 == null) {
            $user->followingStatus = 0;
        }
        // same valo mane follow kar che to 1
        if ($followingStatus != null) {
            $user->followingStatus = 1;
        }
        // hu same vala ne follow karu chu to 2
        if ($followingStatus2) {
            $user->followingStatus = 2;
        }
        // banne ek bija ne follow kare to 3
        if ($followingStatus && $followingStatus2) {
            $user->followingStatus = 3;
        }

        $fetchUserisLiked = UserNotification::where('my_user_id', $request->my_user_id)
            ->where('user_id', $request->user_id)
            ->where('type', Constants::notificationTypeLikeProfile)
            ->first();

        if ($fetchUserisLiked) {
            $user->is_like = true;
        } else {
            $user->is_like = false;
        }

        return response()->json([
            'status' => true,
            'message' =>  __('app.fetchSuccessful'),
            'data' => $user,
        ]);
    }

    public function updateSavedProfile(Request $req)
    {
        $user = Users::with('images')->where('id', $req->user_id)->first();
        $user->savedprofile = $req->profiles;
        $user->save();

        return response()->json(['status' => true, 'message' => __('app.Updatesuccessful'), 'data' => $user]);
    }

    function getUserDetails(Request $request)
    {

        $data =  Users::with(['workerProfile', 'shopUser', 'package.package', 'promotionPackage.promotionPackage', 'images'])->where('identity', $request->email)->first();

        if ($data != null) {
            $data['image'] = Images::where('user_id', $data['id'])->first();

            $includedTags = ['subscriber_coin', 'saller_coin', 'gift_coin', 'invite_coin'];

            $walletTags = WalletTags::whereIn('tag_name', $includedTags)->get();
            $transactionTypes = [1]; // 1 = add, 2 = deduct, 3 = withdraw

            $walletSummary = collect();

            foreach ($walletTags as $tag) {
                foreach ($transactionTypes as $type) {
                    $matched = $data->walletTransactions()
                        ->where('wallet_tag_id', $tag->id)
                        ->where('transaction_type', $type)
                        ->selectRaw('COUNT(*) as transaction_count, COALESCE(SUM(amount),0) as total_amount')
                        ->first();

                    $walletSummary->push([
                        'wallet_tag_id' => $tag->id,
                        'tag_name' => $tag->tag_name,
                        'transaction_type' => $type,
                        'transaction_count' => $matched->transaction_count ?? 0,
                        'total_amount' => $matched->total_amount ?? 0,
                    ]);
                }
            }

            $data['wallet_summary'] = $walletSummary;
        } else {
            return response()->json([
                'status' => false,
                'message' => __('app.UserNotFound')
            ]);
        }

        $data['password'] = '';

        return response()->json([
            'status' => true,
            'message' => __('app.fetchSuccessful'),
            'data' => $data
        ]);
    }

    public function followUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $fromUserQuery = Users::query();
        $toUserQuery = Users::query();

        $fromUser = $fromUserQuery->where('id', $request->my_user_id)->first();
        $toUser = $toUserQuery->where('id', $request->user_id)->first();

        if ($fromUser && $toUser) {
            if ($fromUser == $toUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Lol you did not follow yourself',
                ]);
            } else {
                $followingList = FollowingList::where('my_user_id', $request->my_user_id)->where('user_id', $request->user_id)->first();
                if ($followingList) {
                    return response()->json([
                        'status' => false,
                        'message' => 'User is Already in following list',
                    ]);
                }

                $blockUserIds = explode(',', $fromUser->blocked_users);

                foreach ($blockUserIds as $blockUserId) {
                    if ($blockUserId == $request->user_id) {
                        return response()->json([
                            'status' => false,
                            'message' => 'You blocked this User',
                        ]);
                    }
                }

                $following = new FollowingList();
                $following->my_user_id = (int) $request->my_user_id;
                $following->user_id = (int) $request->user_id;
                $following->save();

                $followingCount = $fromUserQuery->where('id', $request->my_user_id)->first();
                $followingCount->following += 1;
                $followingCount->save();

                $followersCount = $toUserQuery->where('id', $request->user_id)->first();
                $followersCount->followers += 1;
                $followersCount->save();

                if ($toUser->is_notification == 1) {
                    $notificationDesc = $fromUser->fullname . ' has stared following you.';
                    Myfunction::sendPushToUser(env('APP_NAME'), $notificationDesc, $toUser->device_token);
                }

                $updatedUser = Users::where('id', $request->user_id)->first();

                $updatedUser->images;

                $following->user = $updatedUser;

                $type = Constants::notificationTypeFollow;

                $userNotification = new UserNotification();
                $userNotification->my_user_id = (int) $request->my_user_id;
                $userNotification->user_id = (int) $request->user_id;
                $userNotification->type = $type;
                $userNotification->save();

                return response()->json([
                    'status' => true,
                    'message' => 'User Added in Following List',
                    'data' => $following,
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User Not Found',
            ]);
        }
    }

    public function fetchFollowingList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
            'start' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->my_user_id)->first();
        $blockUserIds = explode(',', $user->blocked_users);

        $fetchFollowingList = FollowingList::whereRelation('user', 'is_block', 0)
            ->whereNotIn('user_id', $blockUserIds)
            ->where('my_user_id', $request->my_user_id)
            // ->with('user', 'user.images')
            ->with(['user' => function ($query) {
                $query->whereHas('images');
            }, 'user.images'])
            ->offset($request->start)
            ->limit($request->limit)
            ->get()
            ->pluck('user');

        return response()->json([
            'status' => true,
            'message' => 'Fetch Following List',
            'data' => $fetchFollowingList,
        ]);
    }

    public function fetchFollowersList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'start' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $fetchFollowersList = FollowingList::where('user_id', $request->user_id)
            ->whereNotIn('my_user_id', function ($query) use ($request) {
                $query->select('id')
                    ->from('users')
                    ->whereRaw("FIND_IN_SET(?, blocked_users)", [$request->user_id]);
            })
            ->with('followerUser', 'followerUser.images')
            ->offset($request->start)
            ->limit($request->limit)
            ->get()
            ->pluck('followerUser');

        return response()->json([
            'status' => true,
            'message' => 'Fetch Followers List',
            'data' => $fetchFollowersList,
        ]);
    }

    public function unfollowUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }


        $fromUserQuery = Users::query();
        $toUserQuery = Users::query();

        $fromUser = $fromUserQuery->where('id', $request->my_user_id)->first();
        $toUser = $toUserQuery->where('id', $request->user_id)->first();

        if ($fromUser && $toUser) {
            if ($fromUser == $toUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Lol You did not Remove yourself, Bcz You dont follow yourself',
                ]);
            } else {
                $followingList = FollowingList::where('my_user_id', $request->my_user_id)->where('user_id', $request->user_id)->first();
                if ($followingList) {
                    $followingCount = $fromUserQuery->where('id', $request->my_user_id)->first();
                    $followingCount->following = max(0, $followingCount->following - 1);
                    $followingCount->save();

                    $followersCount = $toUserQuery->where('id', $request->user_id)->first();
                    $followersCount->followers = max(0, $followersCount->followers - 1);;
                    $followersCount->save();

                    $userNotification = UserNotification::where('my_user_id', $request->my_user_id)
                        ->where('user_id', $request->user_id)
                        ->where('type', Constants::notificationTypeFollow)
                        ->get();
                    $userNotification->each->delete();

                    $followingList->delete();

                    return response()->json([
                        'status' => true,
                        'message' => 'Unfollow user',
                        'data' => $followingList,
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'User Not Found',
                    ]);
                }
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User Not Found',
            ]);
        }
    }

    public function fetchHomePageData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->my_user_id)->first();

        if ($user) {

            $blockUserIds = explode(',', $user->block_user_ids);

            $followingUsers = FollowingList::where('my_user_id', $request->my_user_id)
                ->whereRelation('story', 'created_at', '>=', now()->subDay()->toDateTimeString())
                ->with('user', 'user.images')
                ->whereRelation('user', 'is_block', 0)
                ->get()
                ->pluck('user');

            foreach ($followingUsers as $followingUser) {
                $stories = Story::where('user_id', $followingUser->id)
                    ->where('created_at', '>=', now()->subDay()->toDateTimeString())
                    ->get();

                foreach ($stories as $story) {
                    $story->storyView = $story->view_by_user_ids ? in_array($request->my_user_id, explode(',', $story->view_by_user_ids)) : false;
                }
                $followingUser->stories = $stories;
            }

            $fetchPosts = Post::with('content')
                ->inRandomOrder()
                ->with(['user', 'user.stories', 'user.images'])
                ->whereRelation('user', 'is_block', 0)
                ->whereNotIn('user_id', array_merge($blockUserIds))
                ->limit(10)
                ->get();


            if (!$fetchPosts->isEmpty()) {

                foreach ($fetchPosts as $fetchPost) {
                    $isPostLike = Like::where('user_id', $request->my_user_id)->where('post_id', $fetchPost->id)->first();
                    if ($isPostLike) {
                        $fetchPost->is_like = 1;
                    } else {
                        $fetchPost->is_like = 0;
                    }
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Fetch posts',
                    'data' =>  [
                        'users_stories' => $followingUsers,
                        'posts' => $fetchPosts,
                    ]

                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Posts not Available',
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Fetch Home Page Data Successfully',
                'data' =>  [
                    'users_stories' => $followingUser,
                    'posts' => $fetchPosts,
                ]
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User Not Found',
            ]);
        }
    }

    public function deleteUserFromAdmin(Request $request)
    {
        $rules = [
            'user_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->user_id)->first();
        if ($user == null) {
            return json_encode([
                'status' => false,
                'message' => 'user not found!',
            ]);
        }

        Images::where('user_id', $user->id)->delete();

        $likes = Like::where('user_id', $user->id)->get();
        foreach ($likes as $like) {
            $postLikeCount = Post::where('id', $like->post_id)->first();
            $postLikeCount->likes_count -= 1;
            $postLikeCount->save();
            $like->delete();
        }
        $comments = Comment::where('user_id', $user->id)->get();
        foreach ($comments as $comment) {
            $postCommentCount = Post::where('id', $comment->post_id)->first();
            $postCommentCount->comments_count -= 1;
            $postCommentCount->save();
            $comment->delete();
        }

        $posts = Post::where('user_id', $user->id)->get();
        foreach ($posts as $post) {
            $postContents = PostContent::where('post_id', $post->id)->get();
            foreach ($postContents as $postContent) {
                GlobalFunction::deleteFile($postContent->content);
                GlobalFunction::deleteFile($postContent->thumbnail);
                $postContent->delete();
            }

            UserNotification::where('post_id', $post->id)->delete();

            $post->delete();
        }

        $stories = Story::where('user_id', $user->id)->get();
        foreach ($stories as $story) {
            GlobalFunction::deleteFile($story->content);
            $story->delete();
        }

        $followerList = FollowingList::where('my_user_id', $user->id)->get();
        foreach ($followerList as $follower) {
            $followerUser = User::where('id', $follower->user_id)->first();
            $followerUser->followers -= 1;
            $followerUser->save();

            $follower->delete();
        }

        $followingList = FollowingList::where('user_id', $user->id)->get();
        foreach ($followingList as $following) {
            $followingUser = User::where('id', $following->user_id)->first();
            $followingUser->following -= 1;
            $followingUser->save();

            $following->delete();
        }


        UserNotification::where('user_id', $user->id)->delete();
        LiveApplications::where('user_id', $user->id)->delete();
        LiveHistory::where('user_id', $user->id)->delete();
        RedeemRequest::where('user_id', $user->id)->delete();
        VerifyRequest::where('user_id', $user->id)->delete();
        Report::where('user_id', $user->id)->delete();
        UserNotification::where('my_user_id', $user->id)->delete();
        UserNotification::where('my_user_id', $user->id)->orWhere('user_id', $user->id)->delete();
        $user->delete();

        return response()->json(['status' => true, 'message' => "Account Deleted Successfully !"]);
    }

    // buyUserPackage
    public function buyUserPackage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            // Attempt to find the package
            $package = Packages::findOrFail($request->package_id);

            // Check if the user has an existing active package
            $existingActivePackage = UserPackageTransactions::where('user_id', $request->user_id)
                ->where('status', 'active')
                ->first();

            if ($existingActivePackage) {
                // Update the existing active package to "remove" and "inactive"
                $existingActivePackage->update([
                    'action' => 'remove',
                    'status' => 'inactive',
                    'end_date' => now(),
                ]);
            }

            // Create a new user wallet transaction 
            // $user->wallet -= $request->amount;
            $result = GlobalFunction::minusCoinsFromWallet($request->user_id, $package->price, 2, 3);

            if ($result->getStatusCode() !== 200) {
                return response()->json([
                    'status' => false,
                    'message' => $result->getData()->message,
                ], $result->getStatusCode());
            } else {
                // Update or create user package
                $userPackage = UserPackages::updateOrCreate(
                    ['user_id' => $request->user_id],
                    [
                        'package_id' => $package->id,
                        'start_date' => now(),
                        'end_date' => now()->addDays($package->duration_days),
                    ]
                );

                // Log the transaction for assigning a new package
                UserPackageTransactions::create([
                    'user_id' => $request->user_id,
                    'package_id' => $package->id,
                    'action' => 'assign',
                    'start_date' => now(),
                    'end_date' => now()->addDays($package->duration_days),
                    'status' => 'active',
                    'created_by_user_id' => $request->user_id,
                ]);
                Log::info("Update user package [UserId]: $request->user_id By User [PackageName]: {$package->name} [EndDate] {$userPackage->end_date}");
                return response()->json([
                    'status' => true,
                    'message' => 'coins deducted from wallet successfully',
                    'wallet' => $result->getData()->user->wallet,
                    'total_collected' => $result->getData()->user->total_collected,
                    'transaction' => $result->getData()->transaction,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to update user package for user $request->user_id: " . $e->getMessage());
        }
    }

    // buyPromotionPackage
    public function buyPromotionPackage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'promotion_package_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            // Attempt to find the package
            $promotionPackage = PromotionPackage::findOrFail($request->promotion_package_id);

            // Check if the user has an existing active package
            $existingActivePackage =  PromotionPackageTransactions::where('user_id', $request->user_id)
                ->where('status', 'active')
                ->first();

            if ($existingActivePackage) {
                // Update the existing active package to "remove" and "inactive"
                $existingActivePackage->update([
                    'action' => 'remove',
                    'status' => 'inactive',
                    'end_date' => now(),
                ]);
            }

            // Create a new user wallet transaction 
            // $user->wallet -= $request->amount;
            $result = GlobalFunction::minusCoinsFromWallet($request->user_id, $promotionPackage->price, 2, 4);

            if ($result->getStatusCode() !== 200) {
                return response()->json([
                    'status' => false,
                    'message' => $result->getData()->message,
                ], $result->getStatusCode());
            } else {
                // Update or create user package
                $userPromotionPackage = UserPromotionPackage::updateOrCreate(
                    ['user_id' => $request->user_id],
                    [
                        'promotion_package_id' => $promotionPackage->id,
                        'start_date' => now(),
                        'end_date' => now()->addDays($promotionPackage->duration_days),
                    ]
                );

                // Log the transaction for assigning a new package
                PromotionPackageTransactions::create([
                    'user_id' => $request->user_id,
                    'promotion_package_id' => $promotionPackage->id,
                    'action' => 'assign',
                    'start_date' => now(),
                    'end_date' => now()->addDays($promotionPackage->duration_days),
                    'status' => 'active',
                    'created_by_user_id' => $request->user_id,
                ]);
                Log::info("Update promotion package [UserId]: $request->user_id By User [PackageName]: {$promotionPackage->name} [EndDate] {$userPromotionPackage->end_date}");

                return response()->json([
                    'status' => true,
                    'message' => 'coins deducted from wallet successfully',
                    'wallet' => $result->getData()->user->wallet,
                    'total_collected' => $result->getData()->user->total_collected,
                    'transaction' => $result->getData()->transaction,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to update promotion package for user $request->user_id: " . $e->getMessage());
        }
    }

    // getUserWalletTransactions by walletTag
    public function getUserWalletTransactions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wallet_tag_id' => 'required|integer|exists:wallet_tags,id',
            'user_id' => 'required|integer|exists:users,id',
            'start' => 'required|integer|min:0',
            'limit' => 'required|integer|min:1|max:100',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => __('validation.failed'),
                'errors' => $validator->errors()
            ], 422);
        }

        $walletTagId = $request->wallet_tag_id;
        $userId = $request->user_id;
        $start = $request->start;
        $limit = $request->limit;

        $query = WalletTransactions::with('walletTag')
            ->where('wallet_tag_id', $walletTagId)
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        $total = $query->count();

        $transactions = $query->skip($start)
            ->take($limit)
            ->get();

        return response()->json([
            'status' => true,
            'message' => __('app.fetchSuccessful'),
            'data' => [
                'total' => $total,
                'transactions' => $transactions
            ]
        ]);
    }

    public function getInvitedUsers(Request $request)
    {
        $userId = $request->input('user_id');

        if (!$userId) {
            return response()->json(['status' => false, 'message' => 'user_id is required'], 400);
        }

        $perPage = $request->input('per_page', 10);

        $user = Users::find($userId);

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        // ดึงพร้อม invitee และ invitee.images
        $invited = Invites::with(['invitee.images'])
            ->where('inviter_id', $userId)
            ->paginate($perPage);

        // ดึงข้อมูล invitee พร้อมภาพ
        $inviteeUsers = $invited->getCollection()->pluck('invitee')->filter();

        return response()->json([
            'status' => true,
            'invited_users' => $inviteeUsers->values(),
            'meta' => [
                'current_page' => $invited->currentPage(),
                'last_page' => $invited->lastPage(),
                'per_page' => $invited->perPage(),
                'total' => $invited->total(),
            ]
        ]);
    }
    public function invitee($id)
    {
        $user = Users::where('id', $id)->first();
        return view('invitee', ['user' => $user]);
    }

    public function fetchUserInvitees(Request $request)
    {
        $userId = $request->input('userId');

        if (!$userId) {
            return response()->json(['status' => false, 'message' => 'userId is required'], 400);
        }

        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);
        $draw   = $request->input('draw');
        $search = $request->input('search.value');

        // นับทั้งหมดก่อนกรอง
        $totalData = Invites::where('inviter_id', $userId)->count();

        // คิวรี่หลัก
        $query = Invites::with(['invitee.images'])
            ->where('inviter_id', $userId);

        // ถ้ามีคำค้นหา (search)
        if (!empty($search)) {
            $query->whereHas('invitee', function ($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                    ->orWhere('identity', 'like', "%{$search}%");
            });
        }

        $totalFiltered = $query->count();

        // Ordering (optional)
        $query->offset($start)->limit($length)->orderBy('id', 'desc');

        $invites = $query->get();

        // เตรียมข้อมูลสำหรับ DataTable
        $data = [];

        foreach ($invites as $invite) {
            $user = $invite->invitee;
            if (!$user) continue;

            $imageUrl = count($user->images) > 0
                ? asset($user->images[0]->image)
                : 'http://placehold.jp/150x150.png';

            $data[] = [
                '<img src="' . $imageUrl . '" width="50" height="50">',
                $user->fullname,
                $user->identity,
                '<a href="' . route('viewUserDetails', $user->id) . '" class="btn btn-primary text-white"><i class="fas fa-eye"></i></a>',
            ];
        }

        return response()->json([
            "draw"            => intval($draw),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalFiltered,
            "data"            => $data,
        ]);
    }
}

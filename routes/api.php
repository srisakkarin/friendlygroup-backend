<?php

use App\Http\Controllers\Api\RewardController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\UserLoyaltyController;
use App\Http\Controllers\CartsController;
use App\Http\Controllers\DiamondPackController;
use App\Http\Controllers\GiftsController;
use App\Http\Controllers\InterestController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\JobRequestsController;
use App\Http\Controllers\LiveApplicationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PackagesController;
use App\Http\Controllers\POController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PromotionPackageController;
use App\Http\Controllers\RedeemRequestsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShopMainCategoryController;
use App\Http\Controllers\ShopProductController;
use App\Http\Controllers\ShopUserController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WorkerProfileController;
use Illuminate\Routing\RouteGroup;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, Origin, Authorization');
// header('Access-Control-Allow-Credentials: true');

//TEST API IS WORKING
Route::get('/', function () {
  return response()->json(['message' => 'API is working!']);
});

//create cors test route
Route::get('/cors-test', function () {
  return response()->json(['message' => 'CORS is working!']);
});



/*|--------------------------------------------------------------------------|
  | Users Route                                                              |
  |--------------------------------------------------------------------------|*/

Route::post('register', [UsersController::class, 'addUserDetails'])->middleware('checkHeader');
Route::post('updateProfile', [UsersController::class, 'updateProfile'])->middleware('checkHeader');
Route::post('fetchUsersByCordinates', [UsersController::class, 'fetchUsersByCordinates'])->middleware('checkHeader');
Route::post('updateUserBlockList', [UsersController::class, 'updateUserBlockList'])->middleware('checkHeader');
Route::post('deleteMyAccount', [UsersController::class, 'deleteMyAccount'])->middleware('checkHeader');
Route::post('getProfile', [UsersController::class, 'getProfile'])->middleware('checkHeader');
Route::post('getUserDetails', [UsersController::class, 'getUserDetails'])->middleware('checkHeader');
Route::post('getRandomProfile', [UsersController::class, 'getRandomProfile']);
Route::post('getExplorePageProfileList', [UsersController::class, 'getExplorePageProfileList']);

Route::post('updateSavedProfile', [UsersController::class, 'updateSavedProfile'])->middleware('checkHeader');
Route::post('updateLikedProfile', [UsersController::class, 'updateLikedProfile'])->middleware('checkHeader');

Route::post('fetchSavedProfiles', [UsersController::class, 'fetchSavedProfiles'])->middleware('checkHeader');
Route::post('fetchLikedProfiles', [UsersController::class, 'fetchLikedProfiles'])->middleware('checkHeader');

Route::post('getPackage', [PackageController::class, 'getPackage'])->middleware('checkHeader');
Route::post('getInterests', [InterestController::class, 'getInterests'])->middleware('checkHeader');
Route::post('addUserReport', [ReportController::class, 'addUserReport'])->middleware('checkHeader');
Route::post('getSettingData', [SettingController::class, 'getSettingData'])->middleware('checkHeader');

Route::post('searchUsers', [UsersController::class, 'searchUsers'])->middleware('checkHeader');
Route::post('searchUsersForInterest', [UsersController::class, 'searchUsersForInterest'])->middleware('checkHeader');

Route::post('getUserNotifications', [NotificationController::class, 'getUserNotifications'])->middleware('checkHeader');
Route::post('getAdminNotifications', [NotificationController::class, 'getAdminNotifications'])->middleware('checkHeader');

Route::post('getDiamondPacks', [DiamondPackController::class, 'getDiamondPacks'])->middleware('checkHeader');

Route::post('onOffNotification', [UsersController::class, 'onOffNotification'])->middleware('checkHeader');
Route::post('updateLiveStatus', [UsersController::class, 'updateLiveStatus'])->middleware('checkHeader');
Route::post('onOffShowMeOnMap', [UsersController::class, 'onOffShowMeOnMap'])->middleware('checkHeader');
Route::post('onOffAnonymous', [UsersController::class, 'onOffAnonymous'])->middleware('checkHeader');
Route::post('onOffVideoCalls', [UsersController::class, 'onOffVideoCalls'])->middleware('checkHeader');

Route::post('fetchBlockedProfiles', [UsersController::class, 'fetchBlockedProfiles'])->middleware('checkHeader');

Route::post('applyForLive', [LiveApplicationController::class, 'applyForLive'])->middleware('checkHeader');
Route::post('applyForVerification', [UsersController::class, 'applyForVerification'])->middleware('checkHeader');

Route::post('addCoinsToWallet', [UsersController::class, 'addCoinsToWallet'])->middleware('checkHeader');
Route::post('minusCoinsFromWallet', [UsersController::class, 'minusCoinsFromWallet']);
Route::post('increaseStreamCountOfUser', [UsersController::class, 'increaseStreamCountOfUser'])->middleware('checkHeader');

Route::post('addLiveStreamHistory', [LiveApplicationController::class, 'addLiveStreamHistory'])->middleware('checkHeader');
Route::post('logOutUser', [UsersController::class, 'logOutUser'])->middleware('checkHeader');
Route::post('fetchAllLiveStreamHistory', [LiveApplicationController::class, 'fetchAllLiveStreamHistory'])->middleware('checkHeader');

Route::post('placeRedeemRequest', [RedeemRequestsController::class, 'placeRedeemRequest'])->middleware('checkHeader');
Route::post('fetchMyRedeemRequests', [RedeemRequestsController::class, 'fetchMyRedeemRequests'])->middleware('checkHeader');
Route::post('pushNotificationToSingleUser', [NotificationController::class, 'pushNotificationToSingleUser'])->middleware('checkHeader');



Route::post('followUser', [UsersController::class, 'followUser'])->middleware('checkHeader');
Route::post('fetchFollowingList', [UsersController::class, 'fetchFollowingList'])->middleware('checkHeader');
Route::post('fetchFollowersList', [UsersController::class, 'fetchFollowersList'])->middleware('checkHeader');
Route::post('unfollowUser', [UsersController::class, 'unfollowUser'])->middleware('checkHeader');

Route::post('fetchHomePageData', [UsersController::class, 'fetchHomePageData'])->middleware('checkHeader');

Route::post('createStory', [PostController::class, 'createStory'])->middleware('checkHeader');
Route::post('viewStory', [PostController::class, 'viewStory'])->middleware('checkHeader');
Route::post('fetchStories', [PostController::class, 'fetchStories'])->middleware('checkHeader');
Route::post('deleteStory', [PostController::class, 'deleteStory'])->middleware('checkHeader');

Route::post('reportPost', [PostController::class, 'reportPost'])->middleware('checkHeader');

Route::post('addPost', [PostController::class, 'addPost'])->middleware('checkHeader');
// Route::post('fetchPosts', [PostController::class, 'fetchPosts'])->middleware('checkHeader');
Route::post('addComment', [PostController::class, 'addComment'])->middleware('checkHeader');
Route::post('fetchComments', [PostController::class, 'fetchComments'])->middleware('checkHeader');
Route::post('deleteComment', [PostController::class, 'deleteComment'])->middleware('checkHeader');
Route::post('likePost', [PostController::class, 'likePost'])->middleware('checkHeader');
Route::post('dislikePost', [PostController::class, 'dislikePost'])->middleware('checkHeader');
Route::post('deleteMyPost', [PostController::class, 'deleteMyPost'])->middleware('checkHeader');
Route::post('fetchPostByUser', [PostController::class, 'fetchPostByUser'])->middleware('checkHeader');
Route::post('fetchPostsByHashtag', [PostController::class, 'fetchPostsByHashtag'])->middleware('checkHeader');
Route::post('fetchPostByPostId', [PostController::class, 'fetchPostByPostId'])->middleware('checkHeader');
Route::post('increasePostViewCount', [PostController::class, 'increasePostViewCount'])->middleware('checkHeader');
// view post
Route::post('viewPost', [PostController::class, 'viewPost'])->middleware('checkHeader');
Route::post('viewPostByUser', [PostController::class, 'viewPostByUser'])->middleware('checkHeader');
Route::post('viewMyPost', [PostController::class, 'viewMyPost'])->middleware('checkHeader');

Route::get('test', [UsersController::class, 'test'])->middleware('checkHeader');

Route::get('deleteStoryFromWeb', [PostController::class, 'deleteStoryFromWeb'])->name('deleteStoryFromWeb');
Route::post('storeFileGivePath', [SettingController::class, 'storeFileGivePath'])->middleware('checkHeader');
Route::post('generateAgoraToken', [SettingController::class, 'generateAgoraToken'])->middleware('checkHeader');

// Wallet
Route::post('getUserWalletTransactions', [UsersController::class, 'getUserWalletTransactions'])->middleware('checkHeader'); //ดึงรายการ wallet transaction ตาม walletTag

// POgetUserWalletTransactions
Route::post('po/create', [POController::class, 'create'])->middleware('checkHeader');
Route::post('webhook/update-po', [WebhookController::class, 'updatePO']);

//SHOP
Route::post('shop/fetchMainShopCategory', [ShopUserController::class, 'fetchMainShopCategory'])->middleware('checkHeader');
Route::post('shop/getShopByUserId', [ShopUserController::class, 'getShopByUserId'])->middleware('checkHeader');
Route::post('shop/openShop', [ShopUserController::class, 'openShop'])->middleware('checkHeader');
Route::post('shop/updateShop', [ShopUserController::class, 'updateShop'])->middleware('checkHeader');

// PROODUCT
Route::post('shop/product/create', [ShopProductController::class, 'create'])->middleware('checkHeader');
Route::post('shop/product/update', [ShopProductController::class, 'update'])->middleware('checkHeader');
Route::post('shop/product/delete', [ShopProductController::class, 'delete'])->middleware('checkHeader');
Route::post('shop/product/allProduct', [ShopProductController::class, 'allProduct'])->middleware('checkHeader'); //สินค้าทั้งหมดของเรา where my_user_id = $request->my_user_id
Route::post('shop/product/getAllProductCategory', [ShopProductController::class, 'getAllProductCategory'])->middleware('checkHeader'); //ประเภทสินค้าทั้งหมด
Route::post('shop/product/getProductById', [ShopProductController::class, 'getProductById'])->middleware('checkHeader'); //ข้อมูลสินค้า
Route::post('shop/product/updateVariants', [ShopProductController::class, 'updateVariants'])->middleware('checkHeader'); //อัพเดทตัวเลือกสินค้า

// WORKER PROFILE
Route::post('worker/createWorkerProfile', [WorkerProfileController::class, 'createWorkerProfile'])->middleware('checkHeader'); //สร้างโปรไฟล์รับงาน
Route::post('worker/updateWorkerProfile', [WorkerProfileController::class, 'updateWorkerProfile'])->middleware('checkHeader'); //แก้ไขโปรไฟล์รับงาน
Route::post('worker/deleteWorkerProfile', [WorkerProfileController::class, 'deleteWorkerProfile'])->middleware('checkHeader'); //ลบโปรไฟล์รับงาน
Route::post('worker/getWorkerProfileByUserId', [WorkerProfileController::class, 'getWorkerProfileByUserId'])->middleware('checkHeader'); //ดึงข้อมูลโปรไฟล์รับงานจาก user_id
//JOB
Route::post('worker/createJob', [JobController::class, 'createJob'])->middleware('checkHeader'); //สร้างงาน
Route::post('worker/updateJob', [JobController::class, 'updateJob'])->middleware('checkHeader'); //แก้ไขงาน
Route::post('worker/getAllJobCategory', [JobController::class, 'getAllJobCategory'])->middleware('checkHeader'); //ดึงข้อมูลประเภทงานทั้งหมด
Route::post('worker/deleteJob', [JobController::class, 'deleteJob'])->middleware('checkHeader'); //ลบงาน
Route::post('worker/deleteJobImage', [JobController::class, 'deleteJobImage'])->middleware('checkHeader'); //ลบรูปภาพงาน
Route::post('worker/getJobByJobId', [JobController::class, 'getJobByJobId'])->middleware('checkHeader'); //ดึงข้อมูลงานจาก job_id
Route::post('worker/getJobByUserId', [JobController::class, 'getJobByUserId'])->middleware('checkHeader'); //ดึงข้อมูลงานทั้งหมดจาก user_id


//USER PACKAGE
Route::post('userPackage/getAllpackage', [PackagesController::class, 'getAllPackages'])->middleware('checkHeader'); //ดึงข้อมูลแพ็คเกจทั้งหมด
Route::post('userPackage/buyUserPackage', [UsersController::class, 'buyUserPackage'])->middleware('checkHeader'); //ซื้อแพ็คเกจ


//PROMOTION PACKAGE
Route::post('promotionPackage/getAllPromotionPackage', [PromotionPackageController::class, 'getPromotionPackages'])->middleware('checkHeader'); //ดึงข้อมูลโปรโมชั่นทั้งหมด
Route::post('promotionPackage/buyPromotionPackage', [UsersController::class, 'buyPromotionPackage'])->middleware('checkHeader'); //ซื้อโปรโมชั่น


//GIFTS
Route::post('gifts/getAllGifts', [GiftsController::class, 'getAllGifts'])->middleware('checkHeader'); //ดึงข้อมูลของ Gift ทั้งหมด
Route::post('gifts/getGiftById', [GiftsController::class, 'getGiftById'])->middleware('checkHeader'); //ดึงข้อมูลของ Gift ตาม id
Route::post('gifts/buyGift', [GiftsController::class, 'buyGift'])->middleware('checkHeader'); //ซื้อ Gift
Route::post('gifts/getSenderGifts', [GiftsController::class, 'getSenderGifts'])->middleware('checkHeader'); //ดึงข้อมูลของ Gift ที่เราส่งไป
Route::post('gifts/getRecipientGifts', [GiftsController::class, 'getRecipientGifts'])->middleware('checkHeader'); //ดึงข้อมูลของ Gift ที่เรารับ

//CART
Route::post('cart/addToCart', [CartsController::class, 'addToCart'])->middleware('checkHeader'); //เพิ่มสินค้าลงตะกร้า
Route::post('cart/getUserCart', [CartsController::class, 'getUserCart'])->middleware('checkHeader'); //ดึงข้อมูลตะกร้าของ user
Route::post('cart/updateCartItemQuantity', [CartsController::class, 'updateCartItemQuantity'])->middleware('checkHeader'); //อัพเดทจำนวนสินค้าในตะกร้า action // 'increase' or 'decrease'
Route::post('cart/checkout', [CartsController::class, 'checkout'])->middleware('checkHeader'); //checkout

//ORDER
Route::post('order/fetchUserOrders', [OrdersController::class, 'fetchUserOrders'])->middleware('checkHeader'); //ดึงข้อมูลรายการสั่งซื้อของ user


// Job Requests Routes (Added)
Route::post('jobRequests/createJobRequest', [JobRequestsController::class, 'createJobRequest'])->middleware('checkHeader'); //สร้างคคำขอจ้างงานใหม่
Route::post('jobRequests/getCustomerJobRequests', [JobRequestsController::class, 'getCustomerJobRequests'])->middleware('checkHeader'); //ดึงข้อมูลคำขอจ้างงาน (ส่วนของลูกค้า)
Route::post('jobRequests/getWorkerJobRequests', [JobRequestsController::class, 'getWorkerJobRequests'])->middleware('checkHeader'); //ดึงข้อมูลคำขอจ้างงาน (ส่วนของรับงาน)
Route::post('jobRequests/jobRequestDetail', [JobRequestsController::class, 'jobRequestDetail'])->middleware('checkHeader'); //รายละเอียดคำขอจ้างงาน
Route::post('jobRequests/updateJobRequestStatus', [JobRequestsController::class, 'update'])->middleware('checkHeader'); //อัพเดทสถานะคำขอจ้างงาน
Route::post('jobRequests/deleteJobRequest', [JobRequestsController::class, 'destroy'])->middleware('checkHeader'); //ลบออกคำขอจ้างงาน

//invitaion
Route::post('invite/invitedUsers',[UsersController::class,'getInvitedUsers'])->middleware('checkHeader'); //ดึงรายชื่อสมาชิกที่ถูกเชิญ

// --- User Loyalty ---
Route::post('/user/points', [UserLoyaltyController::class, 'getPoints'])->middleware('checkHeader');
Route::post('/user/generate-qr', [UserLoyaltyController::class, 'generateMyQr'])->middleware('checkHeader');

// --- Rewards ---
Route::get('/rewards', [RewardController::class, 'index'])->middleware('checkHeader');      // รายการทั้งหมด
Route::get('/rewards/{id}', [RewardController::class, 'show'])->middleware('checkHeader'); // ✅ รายละเอียดรายตัว (Reward Detail)
Route::post('/rewards/redeem', [RewardController::class, 'redeem'])->middleware('checkHeader');

Route::match(['get', 'post'], '/user/rewards', [RewardController::class, 'getMyRewards']); 
Route::match(['get', 'post'], '/user/rewards-detail/{id}', [RewardController::class, 'getMyRewardDetail']);

// --- Staff Operations ---
Route::post('/staff/scan', [StaffController::class, 'scan'])->middleware('checkHeader');
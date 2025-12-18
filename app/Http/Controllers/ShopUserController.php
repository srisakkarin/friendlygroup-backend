<?php

namespace App\Http\Controllers;

use App\Models\ShopMainCategory;
use App\Models\ShopUser;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShopUserController extends Controller
{
    public function fetchMainShopCategory(Request $request)
    {

        try {
            $shopMainCategory = ShopMainCategory::get();
            return response()->json([
                'status' => true,
                'message' => 'Fetch Shop Main Category List',
                'data' => $shopMainCategory,
            ]);
        } catch (\Exception $e) {
            //throw $th; 
            return response()->json([
                'status' => false,
                'message' => 'Error Fetch Shop Main Category List',
                // 'data' => $shopMainCategory,
            ], 500);
        }
    }

    public function getShopByUserId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $myShop = ShopUser::where('shop_users_id', $request->my_user_id)->first();

        if ($myShop === null) {
            return json_encode([
                'status' => false,
                'message' => 'User store not found!',
            ]);
        }

        return json_encode([
            'status' => true,
            'message' => 'Get data successfull',
            'data' => $myShop
        ]);
    }

    public function openShop(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
            'shop_name' => 'required',
            'shop_business_type' => 'required|in:individual,corporate',
            'shop_mcate_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $userShop = ShopUser::where('shop_users_id', $request->my_user_id)->first();

        if ($userShop !== null) {
            return json_encode([
                'status' => false,
                'message' => 'User already has an open store.',
            ]);
        }

        $userOpenShop = ShopUser::create([
            'shop_users_id' => $request->my_user_id,
            'shop_name' => $request->shop_name,
            'shop_business_type' => $request->shop_business_type,
            'shop_mcate_id' => $request->shop_mcate_id,
            'shop_status' => 0
        ]);
        if ($userOpenShop) {
            return json_encode([
                'status' => true,
                'message' => 'User shop open completed',
                'data' => $userOpenShop
            ]);
        }
    }

    public function updateShop(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
            'shop_name' => 'required',
            'shop_business_type' => 'required|in:individual,corporate',
            'shop_mcate_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $userShop = ShopUser::where('shop_users_id', $request->my_user_id)->first();

        if ($userShop === null) {
            return json_encode([
                'status' => false,
                'message' => 'User store not found!',
            ]);
        }

        $result = ShopUser::where('shop_users_id', $request->my_user_id)->update([
            'shop_users_id' => $request->my_user_id,
            'shop_name' => $request->shop_name,
            'shop_business_type' => $request->shop_business_type,
            'shop_mcate_id' => $request->shop_mcate_id,
            'shop_status' => 0
        ]);

        if ($result) {
            return json_encode([
                'status' => true,
                'message' => 'User shop update completed',
                'data' => $result
            ]);
        }
    }
}

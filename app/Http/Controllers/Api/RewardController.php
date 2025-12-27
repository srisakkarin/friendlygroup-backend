<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reward;
use App\Models\Redemption;
use App\Models\PointTransaction;
use App\Models\User;
use App\Models\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RewardController extends Controller
{
    // GET /api/rewards
    public function index()
    {
        $rewards = Reward::where('is_active', true)->get();
        return response()->json(['status' => true, 'data' => $rewards]);
    }

    // POST /api/rewards/redeem/{reward_id}
    public function redeem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'reward_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $rewardId = $request->param('reward_id');
        $user = Users::where('id', $request->user_id)->first();

        // $user = Auth::user();
        $reward = Reward::find($rewardId);

        if (!$reward || !$reward->is_active) {
            return response()->json(['status' => false, 'message' => 'Reward not found or inactive'], 404);
        }

        if ($user->points < $reward->required_points) {
            return response()->json(['status' => false, 'message' => 'Insufficient points'], 400);
        }

        DB::beginTransaction();
        try {
            // 1. Deduct points
            $user->points -= $reward->required_points;
            $user->save();

            // 2. Create Redemption Record
            $redemption = Redemption::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'points_used' => $reward->required_points,
                'is_used' => false,
            ]);

            // 3. Log Transaction
            PointTransaction::create([
                'user_id' => $user->id,
                'amount' => -$reward->required_points,
                'type' => 'use',
                'description' => 'Redeemed reward: ' . $reward->name,
                'related_id' => $redemption->id,
                'related_type' => 'redemption'
            ]);

            DB::commit();

            // Payload for QR Code: fg:reward:{redemption_id}
            $qrPayload = "fg:redeem:" . $redemption->id;

            return response()->json([
                'status' => true,
                'message' => 'Redemption successful',
                'data' => [
                    'redemption_id' => $redemption->id,
                    'qr_code_payload' => $qrPayload,
                    'reward' => $reward
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Error processing redemption'], 500);
        }
    }
}
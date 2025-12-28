<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\Redemption;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    public function scan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required', // ID ของพนักงานที่ทำการสแกน
            'qr_code' => 'required|string',
            'total_price' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        // ตรวจสอบพนักงาน
        $staff = Users::where('id', $request->user_id)->first();

        if (!$staff) {
            return response()->json(['status' => false, 'message' => 'Staff user not found!']);
        }

        if ($staff->role !== 'staff') {
            return response()->json(['status' => false, 'message' => 'Unauthorized. Staff role required.'], 403);
        }

        // Parse QR Code
        $qrData = explode(':', $request->qr_code);
        if (count($qrData) !== 3 || $qrData[0] !== 'fg') {
            return response()->json(['status' => false, 'message' => 'Invalid QR Code format.'], 400);
        }

        $type = $qrData[1]; 
        $id = $qrData[2];   

        if ($type === 'points') {
            return $this->processAddPoints($id, $staff);
        } elseif ($type === 'redeem') {
            return $this->processUseReward($id, $request->total_price, $staff);
        } else {
            return response()->json(['status' => false, 'message' => 'Unknown QR type.'], 400);
        }
    }

    private function processAddPoints($userId, $staff)
    {
        $customer = Users::find($userId);
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer not found.'], 404);
        }

        $pointsToAdd = 10; // Configurable points

        DB::beginTransaction();
        try {
            $customer->points += $pointsToAdd;
            $customer->save();

            PointTransaction::create([
                'user_id' => $customer->id,
                'amount' => $pointsToAdd,
                'type' => 'earn',
                'description' => 'Store Visit (Staff: ' . $staff->fullname . ')',
                'related_id' => $staff->id,
                'related_type' => 'staff_scan'
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Added $pointsToAdd points to {$customer->fullname}",
                'data' => [
                    'current_points' => $customer->points
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Error adding points.'], 500);
        }
    }

    private function processUseReward($redemptionId, $totalPrice, $staff)
    {
        $redemption = Redemption::with('reward', 'user')->find($redemptionId);

        if (!$redemption) {
            return response()->json(['status' => false, 'message' => 'Redemption record not found'], 404);
        }

        if ($redemption->is_used) {
            return response()->json(['status' => false, 'message' => 'This reward has already been used'], 400);
        }

        $reward = $redemption->reward;
        $discountAmount = 0;
        $finalPrice = 0;

        if ($reward->type === 'discount') {
            if (!$totalPrice) {
                return response()->json(['status' => false, 'message' => 'Total price is required for discount calculation.'], 400);
            }

            if ($reward->discount_type === 'percent') {
                $discountAmount = ($totalPrice * $reward->discount_value) / 100;
            } else {
                $discountAmount = $reward->discount_value;
            }

            $finalPrice = max(0, $totalPrice - $discountAmount);
        }

        $redemption->is_used = 1;
        $redemption->used_at = now();
        $redemption->total_price = $totalPrice;
        $redemption->save();

        return response()->json([
            'status' => true,
            'message' => 'Reward redeemed successfully!',
            'data' => [
                'reward_type' => $reward->type,
                'reward_name' => $reward->name,
                'customer_name' => $redemption->user->fullname,
                'original_price' => $totalPrice,
                'discount_amount' => $discountAmount,
                'final_price' => $finalPrice
            ]
        ]);
    }
}
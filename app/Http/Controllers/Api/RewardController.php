<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reward;
use App\Models\Redemption;
use App\Models\PointTransaction;
use App\Models\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode; // อย่าลืมลง package: simple-qrcode

class RewardController extends Controller
{
    // 1. ดูรายการของรางวัลทั้งหมด (Catalog)
    public function index()
    {
        // ดึงเฉพาะรางวัลที่ Active
        $rewards = Reward::where('is_active', 1)
            ->orderBy('required_points', 'asc')
            ->get();

        // แปลง Path รูปภาพให้เป็น Full URL
        $rewards->transform(function ($item) {
            if ($item->image) {
                $item->image_url = asset($item->image);
            }
            return $item;
        });

        return response()->json([
            'status' => true,
            'data' => $rewards
        ]);
    }

    // 2. ดูรายละเอียดของรางวัลรายตัว (Reward Detail) - หน้าดูรายละเอียดก่อนกดแลก
    public function show($id)
    {
        $reward = Reward::find($id);

        if (!$reward) {
            return response()->json(['status' => false, 'message' => 'Reward not found'], 404);
        }

        if ($reward->image) {
            $reward->image_url = asset($reward->image);
        }

        return response()->json([
            'status' => true,
            'data' => $reward
        ]);
    }

    // 3. แลกรางวัล (Redeem)
    public function redeem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'reward_id' => 'required|exists:rewards,id'
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::where('id', $request->user_id)->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found!']);
        }

        $reward = Reward::find($request->reward_id);

        if (!$reward->is_active) {
            return response()->json(['status' => false, 'message' => 'This reward is no longer available.']);
        }

        if ($user->points < $reward->required_points) {
            return response()->json(['status' => false, 'message' => 'Insufficient points.']);
        }

        DB::beginTransaction();
        try {
            // หักแต้ม
            $user->points -= $reward->required_points;
            $user->save();

            // สร้าง Record การแลก
            $redemption = new Redemption();
            $redemption->user_id = $user->id;
            $redemption->reward_id = $reward->id;
            $redemption->points_used = $reward->required_points;
            $redemption->is_used = 0; // ยังไม่ได้ใช้
            $redemption->save();

            // บันทึก Transaction
            PointTransaction::create([
                'user_id' => $user->id,
                'amount' => -$reward->required_points,
                'type' => 'use',
                'description' => 'Redeemed: ' . $reward->name,
                'related_id' => $redemption->id,
                'related_type' => 'redemption'
            ]);

            DB::commit();

            // Format QR: fg:redeem:{redemption_id}
            $qrPayload = "fg:redeem:" . $redemption->id;

            return response()->json([
                'status' => true,
                'message' => 'Redemption successful!',
                'data' => [
                    'redemption_id' => $redemption->id,
                    'qr_code' => $qrPayload,
                    'reward_name' => $reward->name
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Error processing redemption'], 500);
        }
    }

    // 4. ดูรายการคูปองของฉัน (My Rewards / History)
    public function getMyRewards(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'status'  => 'nullable|in:active,used' // กรองสถานะได้ (ถ้าไม่ส่งมาคือเอาทั้งหมด)
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        $query = Redemption::with('reward')
            ->where('user_id', $request->user_id)
            ->orderBy('created_at', 'desc');

        // กรองสถานะ Active (ยังไม่ใช้) หรือ Used (ใช้แล้ว)
        if ($request->has('status')) {
            $isUsed = $request->status === 'used' ? 1 : 0;
            $query->where('is_used', $isUsed);
        }

        $redemptions = $query->get();

        // จัด Format ข้อมูลส่งกลับ
        $data = $redemptions->map(function ($item) {
            return [
                'redemption_id' => $item->id,
                'reward_name' => $item->reward->name,
                'image_url' => $item->reward->image ? asset($item->reward->image) : null,
                'is_used' => (bool) $item->is_used,
                'redeemed_at' => $item->created_at->format('Y-m-d H:i'),
                'used_at' => $item->used_at ? \Carbon\Carbon::parse($item->used_at)->format('Y-m-d H:i') : null,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    // 5. ดูรายละเอียดคูปองเจาะจง + QR Code Image (สำหรับเปิดให้พนักงานสแกน)
    public function getMyRewardDetail(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        // ค้นหา Redemption ตาม ID และ User (ต้องเป็นเจ้าของเท่านั้น)
        $redemption = Redemption::with('reward')
            ->where('id', $id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$redemption) {
            return response()->json(['status' => false, 'message' => 'Coupon not found or access denied.'], 404);
        }

        // 1. สร้าง Payload Text
        $qrPayload = "fg:redeem:" . $redemption->id;

        // 2. สร้าง QR Code Image (Format PNG/SVG, แปลงเป็น Base64)
        $qrBase64 = '';
        try {
            // พยายามใช้ PNG (ต้องการ extension 'imagick' ใน PHP)
            $qrImage = QrCode::format('png')
                ->size(300) // ขนาดภาพ
                ->margin(1) // ขอบขาว
                ->generate($qrPayload);

            $qrBase64 = 'data:image/png;base64,' . base64_encode($qrImage);
        } catch (\Exception $e) {
            // กรณี Server ไม่มี imagick ให้ใช้ svg แทน (Frontend บางตัวอาจต้องใช้ Library แสดงผล SVG)
            $qrImage = QrCode::format('svg')->size(300)->generate($qrPayload);
            $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrImage);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'redemption_id' => $redemption->id,
                'qr_code_payload' => $qrPayload, // เผื่อแอปอยากเอาไป Gen เอง
                'qr_image' => $qrBase64,         // ✅ เอาค่านี้ไปใส่ใน <img src="..."> ได้เลย
                'status' => $redemption->is_used ? 'Used' : 'Active',
                'reward_details' => [
                    'name' => $redemption->reward->name,
                    'description' => $redemption->reward->description,
                    'type' => $redemption->reward->type, // discount / gift
                    'value_display' => $redemption->reward->type == 'discount'
                        ? ($redemption->reward->discount_type == 'percent' ? $redemption->reward->discount_value . '%' : $redemption->reward->discount_value . ' THB')
                        : 'Gift Item',
                    'image_url' => $redemption->reward->image ? asset($redemption->reward->image) : null,
                ],
                'redeemed_at' => $redemption->created_at->format('d M Y, H:i'),
                'expiry_info' => 'Valid at all branches', // ข้อมูลวันหมดอายุ (ถ้ามี)
            ]
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserLoyaltyController extends Controller
{
    public function getPoints(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
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

        // ดึงประวัติธุรกรรมแต้ม 20 รายการล่าสุด
        $transactions = $user->pointTransactions()
            ->select('id', 'amount', 'type', 'description', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'status' => true,
            'data' => [
                'total_points' => $user->points,
                'transactions' => $transactions
            ]
        ]);
    }

    public function generateMyQr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
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

        // Format QR: fg:points:{user_id}
        $qrPayload = "fg:points:" . $user->id;

        // Generate QR Image (Base64)
        $qrBase64 = '';
        try {
            // พยายามใช้ PNG (ต้องการ extension 'imagick' ใน PHP)
            $qrImage = QrCode::format('png')
                ->size(300)
                ->margin(1)
                ->generate($qrPayload);

            $qrBase64 = 'data:image/png;base64,' . base64_encode($qrImage);
        } catch (\Exception $e) {
            // กรณี Server ไม่มี imagick ให้ใช้ svg แทน
            $qrImage = QrCode::format('svg')
                ->size(300)
                ->margin(1)
                ->generate($qrPayload);
            $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrImage);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'qr_code_payload' => $qrPayload, // ส่ง payload ไปด้วยเผื่อจำเป็นต้องใช้
                'qr_image' => $qrBase64,         // ✅ เอาค่านี้ไปใส่ใน <img src="..."> ได้เลย
                'type' => 'earn_points',
                'description' => 'Show this QR to staff to earn points'
            ]
        ]);
    }
}

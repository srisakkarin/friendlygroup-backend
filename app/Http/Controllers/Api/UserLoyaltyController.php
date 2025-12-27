<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode; // ต้อง install package เพิ่ม: simplesoftwareio/simple-qrcode

class UserLoyaltyController extends Controller
{
    // GET /api/user/points
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
            return response()->json(['status' => false, 'message' => 'User Not Found']);
        }
        // $user = Auth::user();

        return response()->json([
            'status' => true,
            'data' => [
                'total_points' => $user->points,
                'transactions' => $user->pointTransactions()->take(20)->get()
            ]
        ]);
    }

    // POST /api/user/generate-points-qr
    public function generatePointsQr(Request $request)
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
            return response()->json(['status' => false, 'message' => 'User Not Found']);
        }
        // Payload pattern: fg:points:{user_id}
        $payload = "fg:points:" . $user->id;

        // ถ้าต้องการส่งเป็น Image Base64
        // $qrImage = base64_encode(QrCode::format('png')->size(200)->generate($payload));

        return response()->json([
            'status' => true,
            'data' => [
                'qr_code_payload' => $payload,
                'description' => 'Show this QR code to staff to earn points.'
            ]
        ]);
    }
}

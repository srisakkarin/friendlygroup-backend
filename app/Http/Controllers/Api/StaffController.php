<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Redemption;
use App\Models\PointTransaction;
use App\Models\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    // POST /api/staff/scan
    public function scan(Request $request)
    {
        

        $request->validate([
            'user_id' => 'required|numeric',
            'qr_code' => 'required|string',
            'total_price' => 'nullable|numeric' // Required if redeeming discount
        ]);

        // Validate Staff Role
        $user = Users::where('id', $request->user_id)->first();
        if ($user->role !== 'staff') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $qrCode = $request->qr_code;
        $parts = explode(':', $qrCode);

        if (count($parts) < 3 || $parts[0] !== 'fg') {
            return response()->json(['status' => false, 'message' => 'Invalid QR Code format'], 400);
        }

        $type = $parts[1]; // 'points' or 'redeem'
        $id = $parts[2];   // user_id or redemption_id

        if ($type === 'points') {
            return $this->processAddPoints($id);
        } elseif ($type === 'redeem') {
            return $this->processRedemption($id, $request->total_price);
        }

        return response()->json(['status' => false, 'message' => 'Unknown QR Type'], 400);
    }

    private function processAddPoints($userId)
    {
        $user = User::find($userId);
        if (!$user) return response()->json(['status' => false, 'message' => 'User not found'], 404);

        // Configurable points per scan (e.g., from settings table)
        $pointsToAdd = 50; 

        DB::transaction(function () use ($user, $pointsToAdd) {
            $user->points += $pointsToAdd;
            $user->save();

            PointTransaction::create([
                'user_id' => $user->id,
                'amount' => $pointsToAdd,
                'type' => 'earn',
                'description' => 'Earned from shop visit (Scanned by Staff)',
                'related_id' => Auth::id(), // Staff ID
                'related_type' => 'staff_scan'
            ]);
        });

        return response()->json([
            'status' => true, 
            'message' => "Added $pointsToAdd points to user {$user->name}",
            'data' => ['current_points' => $user->points]
        ]);
    }

    private function processRedemption($redemptionId, $totalPrice)
    {
        $redemption = Redemption::with('reward')->find($redemptionId);

        if (!$redemption) {
            return response()->json(['status' => false, 'message' => 'Redemption record not found'], 404);
        }

        if ($redemption->is_used) {
            return response()->json(['status' => false, 'message' => 'This reward has already been used'], 400);
        }

        $reward = $redemption->reward;
        $discountAmount = 0;

        if ($reward->type === 'discount') {
            if (!$totalPrice) {
                return response()->json(['status' => false, 'message' => 'Total price required for discount calculation'], 400);
            }

            if ($reward->discount_type === 'percent') {
                $discountAmount = ($totalPrice * $reward->discount_value) / 100;
            } else {
                $discountAmount = $reward->discount_value;
            }
        }

        // Mark as used
        $redemption->update([
            'is_used' => true,
            'used_at' => now(),
            'total_price' => $totalPrice
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Reward redeemed successfully',
            'data' => [
                'type' => $reward->type,
                'reward_name' => $reward->name,
                'discount_amount' => $discountAmount,
                'final_price' => $totalPrice ? ($totalPrice - $discountAmount) : 0
            ]
        ]);
    }
}
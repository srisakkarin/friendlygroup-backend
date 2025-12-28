<?php

namespace App\Http\Controllers\Admin; // หรือ App\Http\Controllers\Admin ตาม structure

use App\Http\Controllers\Controller;
use App\Models\Redemption;
use App\Models\GlobalFunction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RedemptionController extends Controller
{
    public function index()
    {
        return view('redemptions');
    }

    public function getRedemptions(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            // ดึงข้อมูลพร้อม User และ Reward
            $query = Redemption::with(['user', 'reward'])
                ->orderBy('created_at', 'desc');

            // Filter by Status (ถ้ามีส่งมา)
            if ($request->has('status') && $request->status != 'all') {
                $isUsed = $request->status == 'used' ? 1 : 0;
                $query->where('is_used', $isUsed);
            }

            $redemptions = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'status' => true,
                'message' => 'Redemptions fetched successfully.',
                'data' => $redemptions->items(),
                'pagination' => [
                    'total' => $redemptions->total(),
                    'per_page' => $redemptions->perPage(),
                    'current_page' => $redemptions->currentPage(),
                    'last_page' => $redemptions->lastPage(),
                    'from' => $redemptions->firstItem(),
                    'to' => $redemptions->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('RedemptionController: getRedemptions - Error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Error fetching data'], 500);
        }
    }

    // แอดมินกด Mark as Used (กรณีแจกของหน้าร้านแล้วไม่ได้สแกน หรือส่งของแล้ว)
    public function markAsUsed(Request $request)
    {
        try {
            $redemption = Redemption::find($request->id);
            if (!$redemption) {
                return response()->json(['status' => false, 'message' => 'Record not found'], 404);
            }

            $redemption->is_used = true;
            $redemption->used_at = now();
            $redemption->save();

            return response()->json(['status' => true, 'message' => 'Marked as used successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error updating status'], 500);
        }
    }
}

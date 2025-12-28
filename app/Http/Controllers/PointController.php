<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Users; // ✅ แก้เป็น Users ตาม Model ของคุณ
use App\Models\PointTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PointController extends Controller
{
    public function index()
    {
        return view('points');
    }

    public function getUsersPoints(Request $request)
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowPerPage = $request->get("length");

            $columnIndex_arr = $request->get('order');
            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search_arr = $request->get('search');

            $columnIndex = $columnIndex_arr[0]['column'];
            $columnName = $columnName_arr[$columnIndex]['data'];
            $columnSortOrder = $order_arr[0]['dir'];
            $searchValue = $search_arr['value'];

            // ✅ 1. เลือก Field ที่มีจริงใน Users Model ของคุณ
            // ใช้ 'identity' หรือ 'username' แทน email/phone ที่ error
            $users = Users::select('id', 'fullname', 'username', 'identity', 'points', 'created_at'); 

            // ✅ 2. แก้ไขการ Search ให้ตรงกับ Field ที่มี
            if (!empty($searchValue)) {
                $users->where(function($q) use ($searchValue) {
                    $q->where('fullname', 'like', '%' . $searchValue . '%')
                      ->orWhere('username', 'like', '%' . $searchValue . '%')
                      ->orWhere('identity', 'like', '%' . $searchValue . '%');
                });
            }

            $totalRecords = Users::count();
            $totalRecordWithFilter = $users->count();

            // ✅ 3. Sorting
            if ($columnName == 'fullname') {
                $users->orderBy('fullname', $columnSortOrder);
            } elseif (in_array($columnName, ['points', 'username', 'identity'])) {
                $users->orderBy($columnName, $columnSortOrder);
            } else {
                $users->orderBy('points', 'desc');
            }

            $data = $users->skip($start)
                          ->take($rowPerPage)
                          ->get();

            return response()->json([
                "draw" => intval($draw),
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $totalRecordWithFilter,
                "data" => $data
            ]);

        } catch (\Exception $e) {
            Log::error('PointController Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getPointHistory(Request $request)
    {
        try {
            $userId = $request->user_id;
            
            $history = PointTransaction::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->take(50)
                ->get();

            return response()->json([
                'status' => true,
                'data' => $history
            ]);
        } catch (\Exception $e) {
            Log::error('getPointHistory Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Error fetching history'], 500);
        }
    }

    public function adjustPoints(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|integer|min:1',
            'type' => 'required|in:add,deduct',
            'description' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        DB::beginTransaction();
        try {
            // ✅ แก้เป็น Users Model
            $user = Users::find($request->user_id); 
            $amount = (int) $request->amount;
            $txnAmount = 0;

            if ($request->type === 'add') {
                $user->points += $amount;
                $txnAmount = $amount;
            } else {
                if ($user->points < $amount) {
                    return response()->json(['status' => false, 'message' => 'แต้มไม่พอ'], 400);
                }
                $user->points -= $amount;
                $txnAmount = -$amount;
            }

            $user->save();

            PointTransaction::create([
                'user_id' => $user->id,
                'amount' => $txnAmount,
                'type' => 'adjust',
                'description' => 'Admin Adjust: ' . $request->description,
                'related_id' => auth()->id() ?? 0,
                'related_type' => 'admin'
            ]);

            DB::commit();

            return response()->json([
                'status' => true, 
                'message' => 'Success',
                'new_points' => $user->points
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('adjustPoints Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed'], 500);
        }
    }
}
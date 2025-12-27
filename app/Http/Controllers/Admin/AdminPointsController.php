<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\DB;

class AdminPointsController extends Controller
{
    // POST /api/admin/adjust-points
    public function adjust(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|integer', // Can be negative
            'description' => 'required|string'
        ]);

        $user = User::find($request->user_id);

        DB::transaction(function () use ($user, $request) {
            $user->points += $request->amount;
            // Prevent negative balance? 
            if ($user->points < 0) $user->points = 0; 
            $user->save();

            PointTransaction::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'type' => 'adjust',
                'description' => 'Admin adjustment: ' . $request->description,
                'related_id' => Auth::id(), // Admin ID
                'related_type' => 'admin'
            ]);
        });

        return response()->json(['status' => true, 'message' => 'Points adjusted successfully', 'current_points' => $user->points]);
    }
}
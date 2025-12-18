<?php

namespace App\Http\Controllers;

use App\Models\RevenueSharingRule;
use Illuminate\Http\Request;

class RevenueSharingRuleController extends Controller
{
    public function index()
    {
        $rules = RevenueSharingRule::all();
        return view('income-settings.index', compact('rules'));
    }

    public function update(Request $request)
    {
        foreach ($request->input('rules') as $id => $data) {
            if (!is_numeric($data['company_percent']) || !is_numeric($data['customer_percent'])) {
                return response()->json(['status' => 'error', 'message' => 'Invalid percent value.'], 422);
            }

            RevenueSharingRule::where('id', $id)->update([
                'company_percent' => $data['company_percent'],
                'customer_percent' => $data['customer_percent'], 
                'calculate_with' => $data['calculate_with']
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'บันทึกข้อมูลเรียบร้อย!']);
    }
}

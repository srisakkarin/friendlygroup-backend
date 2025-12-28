<?php

namespace App\Http\Controllers;

use App\Models\GlobalFunction;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    public function index()
    {
        return view('shops');
    }
    public function fetchShops(Request $request)
    {
        $totalData = Shop::count();
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = 'id';
        $dir = 'desc';

        if ($request->input('order.0.column') != null) {
            $order = $request->input('columns.' . $request->input('order.0.column') . '.data');
            $dir = $request->input('order.0.dir');
        }

        $query = Shop::query();

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('code', 'LIKE', "%{$search}%");
        }

        $totalFiltered = $query->count();
        $shops = $query->offset($start)->limit($limit)->orderBy($order, $dir)->get();

        $data = [];
        foreach ($shops as $shop) {
            $status = $shop->is_active
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-danger">Inactive</span>';

            $action = '<button class="btn btn-primary btn-sm editShop" data-id="' . $shop->id . '">Edit</button> ';
            $action .= '<button class="btn btn-danger btn-sm deleteShop" data-id="' . $shop->id . '">Delete</button>';

            $logoHtml = '-';
            if ($shop->logo) {
                $imgSrc = $shop->logo;
                $logoHtml = '<img src="' . $imgSrc . '" width="50" height="50" class="rounded-circle" style="object-fit: cover;">';
            }

            $data[] = [
                'logo' => $logoHtml,
                'name' => $shop->name,
                'code' => $shop->code ?? '-',
                'address' => $shop->address ?? '-',
                'is_active' => $status,
                'action' => $action
            ];
        }

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        $shop = null;
        if ($request->id) {
            $shop = Shop::find($request->id);
        }
        $shopData = [
            'name' => $request->name,
            'code' => $request->code,
            'address' => $request->address,
            'is_active' => $request->is_active ?? 1
        ];
        if ($request->has('logo')) {
            if ($shop && $shop->logo) {
                GlobalFunction::deleteFile($shop->logo);
            }
            $shopData['logo'] = GlobalFunction::saveFileAndGivePath($request->logo);
        }
        if ($shop) {
            $shop->update($shopData);
        } else {
            Shop::create($shopData);
        }

        return response()->json(['status' => true, 'message' => 'Shop saved successfully!']);
    }

    public function edit($id)
    {
        $shop = Shop::find($id);
        if ($shop->logo) {
            $shop->logo_url = asset($shop->logo); // หรือ env('image') . $shop->logo
        }
        return response()->json(['status' => true, 'data' => $shop]);
    }

    public function destroy($id)
    {
        $shop = Shop::find($id);
        if ($shop) {
            if ($shop->logo) {
                GlobalFunction::deleteFile($shop->logo);
            }
            $shop->delete();
            return response()->json(['status' => true, 'message' => 'Shop deleted successfully!']);
        }
        return response()->json(['status' => false, 'message' => 'Shop not found!']);
    }
}

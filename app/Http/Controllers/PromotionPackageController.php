<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Packages;
use App\Models\PromotionPackage;
use App\Models\PromotionPackageTransactions;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PromotionPackageController extends Controller
{
    function addPromotionPackages(Request $req)
    {

        $count = PromotionPackage::count();
        if ($count < 3) {
            $pack = new PromotionPackage();

            $pack->description  = $req->description;
            $pack->name = $req->name;
            $pack->duration_days = $req->duration_days;
            $pack->app_id = $req->appid;
            $pack->play_id = $req->playid;
            $pack->price = $req->price;
            $pack->save();

            return json_encode(['status' => true, 'message' => __('app.AddSuccessful')]);
        } else {
            return json_encode(['status' => false, 'message' => __('app.minimumPackage')]);
        }
    }

    function fetchAllPromotionPackages(Request $request)
    {

        $totalData =  PromotionPackage::count();
        $rows = PromotionPackage::orderBy('id', 'DESC')->get();


        $result = $rows;

        $columns = array(
            0 => 'id',
            1 => 'name'
        );
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $totalData = PromotionPackage::count();
        $totalFiltered = $totalData;
        if (empty($request->input('search.value'))) {
            $result = PromotionPackage::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  PromotionPackage::Where('name', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = PromotionPackage::where('id', 'LIKE', "%{$search}%")
                ->orWhere('name', 'LIKE', "%{$search}%")
                ->count();
        }
        // dd($result);
        $data = array();
        foreach ($result as $item) {
            if ($item->is_block == 0) {
                $block  =   '<a href=""  rel="' . $item->id . '"   class="btn btn-primary  edit_cats mr-2"><i class="fas fa-edit"></i></a>
                <a href="'.route('html-content',['modelType' => 'promotionPackage', 'modelId' => $item->id]).'"  rel="' . $item->id . '"   class="btn btn-primary mr-2"><i class="fas fa-file-code"></i></a>
                <a href = ""  rel = "' . $item->id . '" class = "btn btn-danger delete-cat text-white" ><i class="fas fa-trash-alt"></i> </a>';
            }

            $data[] = array(
                '<p>' . $item->name . '</p>',
                '<p>' . $item->description . '</p>',
                '<p>' . $item->price . '</p>',
                '<p>' . $item->duration_days . '</p>',
                '<p>' . $item->app_id . '</p>',
                '<p>' . $item->play_id . '</p>',
                $block
            );
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data"            => $data
        );
        echo json_encode($json_data);
        exit();
    }

    function getPromotionPackagesById($id)
    {
        $data = PromotionPackage::where('id', $id)->first();

        echo json_encode($data);
    }

    function updatePromotionPackages(Request $req)
    {


        $pack = PromotionPackage::find($req->id);

        $pack->description  = $req->description;
        $pack->name = $req->name;
        $pack->duration_days = $req->duration_days;
        $pack->app_id = $req->appid;
        $pack->play_id = $req->playid;
        $pack->price = $req->price;
        $pack->save();

        return json_encode(['status' => true, 'message' => __('app.Updatesuccessful')]);
    }

    function deletePromotionPackages($id)
    {

        $data =  PromotionPackage::where('id', $id);
        $data->delete();

        $data1['status'] = true;

        echo json_encode($data1);
    }

    function getPromotionPackages()
    {
        $data = PromotionPackage::orderBy('id', 'DESC')->get();

        return json_encode(['status' => true, 'message' => __('app.fetchSuccessful'), 'data' => $data]);
    }

    public function promotionPackageTransactionList(Request $request)
    {

        // $twentyFourHoursAgo = Carbon::now()->subDay();

        $totalData = PromotionPackageTransactions::where('user_id', $request->user_id)->count();

        $rows = PromotionPackageTransactions::with('promotionPackage')
            ->where('user_id', $request->user_id)
            ->orderBy('id', 'DESC')
            ->get();

        $result = $rows;

        $columns = [
            0 => 'id'
        ];

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $totalFiltered = $totalData;

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $result = PromotionPackageTransactions::with('promotionPackage')
                ->where('user_id', $request->user_id)
                ->where(function ($query) use ($search) {
                    $query->whereHas('user', function ($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%");
                        // Add more conditions for searching other user fields if needed
                    });
                    // Add more conditions for searching other story fields if needed
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = $result->count(); // Count filtered result
        } else {
            $result = PromotionPackageTransactions::with('promotionPackage')
                ->where('user_id', $request->user_id)
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        }

        $data = [];


        foreach ($result as $item) {
            $package = $item->promotionPackage->name;
            $start_date = $item->start_date;
            $end_date = $item->end_date;
            $create_by = '';
            if ($item->created_by_admin_id !== null) {
                $create_by = Admin::find($item->created_by_admin_id)->user_name;
            }
            if ($item->created_by_user_id !== null) {
                $create_by = Users::find($item->created_by_user_id)->full_name;
            }
            $staus = '';
            if ($item->status === 'active') {
                $staus = '<span class="badge badge-success">Active</span> <i class="fas fa-times-circle ml-2 removePromotionPackage" rel="' . $request->user_id . '" style="cursor: pointer;"></i>';
            } else if ($item->status === 'inactive') {
                $staus = '<span class="badge badge-danger">Inactive</span>';
            } else {
                $staus = '<span class="badge badge-warning">Pending</span>';
            }
            $data[] = [
                $package,
                $start_date,
                $end_date,
                $item->action,
                $create_by,
                Carbon::parse($item->created_at)->diffForHumans(),
                $staus
            ];
        }

        $json_data = [
            'draw' => intval($request->input('draw')),
            'recordsTotal' => intval($totalData),
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ];
        echo json_encode($json_data);
        exit();
    }
}

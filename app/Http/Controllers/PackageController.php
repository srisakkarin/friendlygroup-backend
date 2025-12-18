<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Package;
use App\Models\User;
use App\Models\UserPackageTransactions;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    function addPackage(Request $req)
    {

        $count = Package::count();
        if ($count < 3) {
            $pack = new Package();

            $pack->description  = $req->description;
            $pack->title = $req->title;
            $pack->months = $req->months;
            $pack->appid = $req->appid;
            $pack->playid = $req->playid;
            $pack->price = $req->price;
            $pack->save();

            return json_encode(['status' => true, 'message' => __('app.AddSuccessful')]);
        } else {

            return json_encode(['status' => false, 'message' => __('app.minimumPackage')]);
        }
    }

    function fetchAllPackage(Request $request)
    {

        $totalData =  Package::count();
        $rows = Package::orderBy('id', 'DESC')->get();


        $result = $rows;

        $columns = array(
            0 => 'id',
            1 => 'title'
        );
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $totalData = Package::count();
        $totalFiltered = $totalData;
        if (empty($request->input('search.value'))) {
            $result = Package::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Package::Where('title', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Package::where('id', 'LIKE', "%{$search}%")
                ->orWhere('title', 'LIKE', "%{$search}%")
                ->count();
        }
        $data = array();
        foreach ($result as $item) {


            if ($item->is_block == 0) {
                $block  =   '<a href=""  rel="' . $item->id . '"   class="btn btn-primary  edit_cats mr-2"><i class="fas fa-edit"></i></a><a href = ""  rel = "' . $item->id . '" class = "btn btn-danger delete-cat text-white" > <i class="fas fa-trash-alt"></i> </a>';
            }

            $data[] = array(



                '<p>' . $item->title . '</p>',
                '<p>' . $item->description . '</p>',
                '<p>' . $item->price . '</p>',
                '<p>' . $item->months . '</p>',
                '<p>' . $item->appid . '</p>',
                '<p>' . $item->playid . '</p>',
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

    function getPackageById($id)
    {
        $data = Package::where('id', $id)->first();

        echo json_encode($data);
    }

    function updatePackage(Request $req)
    {


        $pack = Package::find($req->id);

        $pack->description  = $req->description;
        $pack->title = $req->title;
        $pack->months = $req->months;
        $pack->appid = $req->appid;
        $pack->playid = $req->playid;
        $pack->price = $req->price;
        $pack->save();

        return json_encode(['status' => true, 'message' => __('app.Updatesuccessful')]);
    }

    function deletePackage($id)
    {

        $data =  Package::where('id', $id);
        $data->delete();

        $data1['status'] = true;

        echo json_encode($data1);
    }

    function getPackage()
    {
        $data = Package::orderBy('id', 'DESC')->get();

        return json_encode(['status' => true, 'message' => __('app.fetchSuccessful'), 'data' => $data]);
    }

    public function userPackageTransactionList(Request $request)
    {

        // $twentyFourHoursAgo = Carbon::now()->subDay();

        $totalData = UserPackageTransactions::where('user_id', $request->user_id)->count();

        $rows = UserPackageTransactions::with('package')
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
            $result = UserPackageTransactions::with('package')
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
            $result = UserPackageTransactions::with('package')
                ->where('user_id', $request->user_id)
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        }

        $data = [];


        foreach ($result as $item) {
            $package = $item->package->name;
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
                $staus = '<span class="badge badge-success">Active</span> <i class="fas fa-times-circle ml-2 removeUserPackage" rel="' . $request->user_id . '" style="cursor: pointer;"></i>';
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

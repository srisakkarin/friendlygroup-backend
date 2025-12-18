<?php

namespace App\Http\Controllers;

use App\Models\Packages;
use Illuminate\Http\Request;

class PackagesController extends Controller
{
    function addPackages(Request $req)
    {

        $count = Packages::count();
        if ($count < 3) {
            $pack = new Packages();

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

    function fetchAllPackages(Request $request)
    {

        $totalData =  Packages::count();
        $rows = Packages::orderBy('id', 'DESC')->get();


        $result = $rows;

        $columns = array(
            0 => 'id',
            1 => 'name'
        );
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $totalData = Packages::count();
        $totalFiltered = $totalData;
        if (empty($request->input('search.value'))) {
            $result = Packages::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Packages::Where('name', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Packages::where('id', 'LIKE', "%{$search}%")
                ->orWhere('name', 'LIKE', "%{$search}%")
                ->count();
        }
        // dd($result);
        $data = array();
        foreach ($result as $item) {
            if ($item->is_block == 0) {
                $block  =   '<a href=""  rel="' . $item->id . '"   class="btn btn-primary  edit_cats mr-2"><i class="fas fa-edit"></i></a>
                <a href="'.route('html-content',['modelType' => 'packages', 'modelId' => $item->id]).'"  rel="' . $item->id . '"   class="btn btn-primary mr-2"><i class="fas fa-file-code"></i></a>
                <a href = ""  rel = "' . $item->id . '" class = "btn btn-danger delete-cat text-white" > <i class="fas fa-trash-alt"></i> </a>';
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

    function getPackagesById($id)
    {
        $data = Packages::where('id', $id)->first();

        echo json_encode($data);
    }

    function updatePackages(Request $req)
    {


        $pack = Packages::find($req->id);

        $pack->description  = $req->description;
        $pack->name = $req->name;
        $pack->duration_days = $req->duration_days;
        $pack->app_id = $req->appid;
        $pack->play_id = $req->playid;
        $pack->price = $req->price;
        $pack->save();

        return json_encode(['status' => true, 'message' => __('app.Updatesuccessful')]);
    }

    function deletePackages($id)
    {

        $data =  Packages::where('id', $id);
        $data->delete();

        $data1['status'] = true;

        echo json_encode($data1);
    }

    function getPackages()
    {
        $data = Packages::orderBy('id', 'DESC')->get();
        return json_encode(['status' => true, 'message' => __('app.fetchSuccessful'), 'data' => $data]);
    } 
    function getAllPackages()
    {
        $data = Packages::orderBy('id', 'DESC')->get();
        return json_encode(['status' => true, 'message' => __('app.fetchSuccessful'), 'data' => $data]);
    } 

}

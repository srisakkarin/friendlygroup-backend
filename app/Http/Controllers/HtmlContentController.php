<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HtmlContentController extends Controller
{
    public function index(Request $request,$modelType,$modelId)
    {
        return view('htmlContent');
    }
}

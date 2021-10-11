<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function index()
    {
        return view(backpack_view('export'));
    }

    public function export()
    {
        dd(request()->all());
    }
}

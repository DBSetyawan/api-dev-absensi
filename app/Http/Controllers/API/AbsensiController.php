<?php

namespace App\Http\Controllers\API;

use App\Present;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AbsensiController extends Controller
{
    public function present_test_api() {
        $data = Present::whereTanggal(date('Y-m-d'))->orderBy('jam_masuk')->paginate(6);
        return response()->json($data, 200);
    }

    public function present_test_apiAuth() {
        $data = "Welcome " . Auth::user()->name;
        return response()->json($data, 200);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return response()->json(['data' => []]);
    }

    public function update(Request $request)
    {
        return response()->json(['message' => 'settings updated']);
    }

    public function reset(Request $request)
    {
        return response()->json(['message' => 'settings reset']);
    }
}

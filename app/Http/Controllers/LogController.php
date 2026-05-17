<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index()
    {
        return response()->json(['data' => []]);
    }

    public function show($id)
    {
        return response()->json(['data' => ['id' => $id]]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'log deleted', 'id' => $id]);
    }

    public function clear(Request $request)
    {
        return response()->json(['message' => 'logs cleared']);
    }
}

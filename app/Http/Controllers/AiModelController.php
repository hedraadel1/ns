<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AiModelController extends Controller
{
    public function index()
    {
        return response()->json(['data' => []]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'ai model created'], 201);
    }

    public function show($id)
    {
        return response()->json(['data' => ['id' => $id]]);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'ai model updated', 'id' => $id]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'ai model deleted', 'id' => $id]);
    }

    public function test(Request $request, $id)
    {
        return response()->json(['message' => 'ai model test executed', 'id' => $id]);
    }
}

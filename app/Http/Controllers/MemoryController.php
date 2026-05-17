<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MemoryController extends Controller
{
    public function index()
    {
        return response()->json(['data' => []]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'memory created'], 201);
    }

    public function show($id)
    {
        return response()->json(['data' => ['id' => $id]]);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'memory updated', 'id' => $id]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'memory deleted', 'id' => $id]);
    }

    public function search(Request $request)
    {
        return response()->json(['data' => []]);
    }

    public function indexMemory(Request $request, $id)
    {
        return response()->json(['message' => 'memory indexed', 'id' => $id]);
    }
}

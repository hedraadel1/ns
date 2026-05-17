<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function index()
    {
        return response()->json(['data' => []]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'agent created'], 201);
    }

    public function show($id)
    {
        return response()->json(['data' => ['id' => $id]]);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'agent updated', 'id' => $id]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'agent deleted', 'id' => $id]);
    }

    public function execute(Request $request, $id)
    {
        return response()->json(['message' => 'agent execution started', 'id' => $id]);
    }

    public function getStatus($id)
    {
        return response()->json(['data' => ['id' => $id, 'status' => 'idle']]);
    }
}

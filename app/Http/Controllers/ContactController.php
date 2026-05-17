<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return response()->json(['data' => []]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'contact created'], 201);
    }

    public function show($id)
    {
        return response()->json(['data' => ['id' => $id]]);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'contact updated', 'id' => $id]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'contact deleted', 'id' => $id]);
    }

    public function getMemory($id)
    {
        return response()->json(['data' => ['contact_id' => $id, 'memories' => []]]);
    }

    public function getRules($id)
    {
        return response()->json(['data' => ['contact_id' => $id, 'rules' => []]]);
    }

    public function getAnalytics($id)
    {
        return response()->json(['data' => ['contact_id' => $id, 'analytics' => []]]);
    }
}

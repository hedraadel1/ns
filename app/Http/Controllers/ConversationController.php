<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index()
    {
        return response()->json(['data' => []]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'conversation created'], 201);
    }

    public function show($id)
    {
        return response()->json(['data' => ['id' => $id]]);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'conversation updated', 'id' => $id]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'conversation deleted', 'id' => $id]);
    }

    public function getMessages($id)
    {
        return response()->json(['data' => ['conversation_id' => $id, 'messages' => []]]);
    }

    public function sendMessage(Request $request, $id)
    {
        return response()->json(['message' => 'message sent', 'conversation_id' => $id]);
    }
}

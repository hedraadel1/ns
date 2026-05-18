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
        return response()->json([
            'data' => [
                'conversation_id' => $id,
                'messages' => [
                    [
                        'id' => 1,
                        'conversation_id' => $id,
                        'sender' => 'user',
                        'channel' => 'chat',
                        'thread_id' => null,
                        'content' => 'This is a sample message',
                        'metadata' => ['source' => 'conversation_api'],
                        'created_at' => now()->toDateTimeString(),
                    ],
                ],
            ],
        ]);
    }

    public function sendMessage(Request $request, $id)
    {
        $validated = $request->validate([
            'sender' => 'required|string',
            'content' => 'required|string',
            'channel' => 'sometimes|string',
            'thread_id' => 'sometimes|string',
            'metadata' => 'sometimes|array',
        ]);

        return response()->json([
            'message' => 'message sent',
            'data' => array_merge($validated, [
                'conversation_id' => $id,
                'id' => rand(1000, 9999),
                'created_at' => now()->toDateTimeString(),
            ]),
        ], 201);
    }
}

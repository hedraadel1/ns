<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        return response()->json(['message' => 'login endpoint placeholder'], 200);
    }

    public function register(Request $request)
    {
        return response()->json(['message' => 'register endpoint placeholder'], 201);
    }

    public function verifyToken(Request $request)
    {
        return response()->json(['message' => 'verify token placeholder'], 200);
    }

    public function logout(Request $request)
    {
        return response()->json(['message' => 'logout endpoint placeholder'], 200);
    }

    public function refreshToken(Request $request)
    {
        return response()->json(['message' => 'refresh token placeholder'], 200);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handleWahaWebhook(Request $request)
    {
        return response()->json(['message' => 'WAHA webhook received'], 200);
    }
}

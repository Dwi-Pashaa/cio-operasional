<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\XenditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    /**
     * Handle Xendit webhook callback from Central Router or direct Xendit.
     * Route: POST /api/xendit/callback
     */
    public function handleCallback(Request $request, XenditService $xenditService): JsonResponse
    {
        $payload = $request->all();
        $token   = $request->header('x-callback-token') 
                ?? $request->header('X-Callback-Token') 
                ?? $request->header('webhook-id');

        Log::info('[XenditWebhookController] Incoming webhook request', [
            'headers' => $request->headers->all(),
            'payload' => $payload,
        ]);

        $result = $xenditService->handleWebhook($payload, $token);

        return response()->json($result, $result['status'] ? 200 : 400);
    }
}

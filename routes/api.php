<?php

use App\Http\Controllers\Api\XenditWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Xendit Webhook Receiver (Central Router Multi-Website)
Route::post('/xendit/callback', [XenditWebhookController::class, 'handleCallback'])->name('api.xendit.callback');

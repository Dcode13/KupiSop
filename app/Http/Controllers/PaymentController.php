<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Webhook notifikasi Midtrans (server-to-server).
     * SDK memverifikasi keaslian dengan memanggil ulang status API.
     */
    public function notify(MidtransService $midtrans): JsonResponse
    {
        try {
            $midtrans->handleNotification();
        } catch (\Throwable $e) {
            Log::warning('Midtrans notification error: '.$e->getMessage());

            // Tetap balas 200 agar Midtrans tidak terus mengulang untuk error jinak.
            return response()->json(['message' => 'ignored'], 200);
        }

        return response()->json(['message' => 'ok']);
    }
}

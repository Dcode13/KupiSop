<?php

namespace App\Services;

use App\Models\Transaction;
use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Notification;
use Midtrans\Transaction as MidtransTransaction;

class MidtransService
{
    /** Metode pembayaran yang didukung (Core API). */
    public const METHODS = ['qris', 'gopay', 'dana', 'bca', 'bni', 'bri'];

    public function __construct()
    {
        Config::$serverKey = (string) config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production');
        Config::$isSanitized = (bool) config('services.midtrans.is_sanitized');
        Config::$is3ds = (bool) config('services.midtrans.is_3ds');
    }

    /**
     * Charge sebuah transaksi dengan metode tertentu via Core API.
     * Menyimpan order_id (payment_ref) unik + detail pembayaran ternormalisasi.
     *
     * @return array<string, mixed>  Detail pembayaran (qr_url / va_number / deeplink / expiry).
     */
    public function charge(Transaction $transaction, string $method): array
    {
        if (! in_array($method, self::METHODS, true)) {
            throw new \InvalidArgumentException("Metode pembayaran '{$method}' tidak didukung.");
        }

        $transaction->loadMissing('items');

        // order_id unik per percobaan agar pelanggan bisa berganti metode.
        $orderId = $transaction->invoice_number.'-'.strtoupper(substr(uniqid(), -5));

        $items = $transaction->items->map(fn ($item) => [
            'id' => (string) ($item->product_id ?? $item->id),
            'price' => (int) round($item->price),
            'quantity' => (int) $item->quantity,
            'name' => mb_substr($item->product_name, 0, 50),
        ])->values()->all();

        $gross = collect($items)->sum(fn ($i) => $i['price'] * $i['quantity']);

        $params = [
            'transaction_details' => ['order_id' => $orderId, 'gross_amount' => $gross],
            'item_details' => $items,
            'customer_details' => ['first_name' => $transaction->customer_name ?: 'Pelanggan'],
        ];

        // Parameter spesifik per metode.
        $params += match ($method) {
            'qris', 'dana' => ['payment_type' => 'qris', 'qris' => ['acquirer' => 'gopay']],
            'gopay' => ['payment_type' => 'gopay', 'gopay' => ['enable_callback' => false]],
            'bca', 'bni', 'bri' => ['payment_type' => 'bank_transfer', 'bank_transfer' => ['bank' => $method]],
        };

        $response = CoreApi::charge($params);

        $details = $this->normalize($method, $response);

        $transaction->update([
            'payment_method' => $method,
            'payment_status' => Transaction::PAY_PENDING,
            'payment_ref' => $orderId,
            'payment_details' => $details,
        ]);

        return $details;
    }

    /**
     * Ubah respons Core API menjadi bentuk seragam untuk UI.
     *
     * @return array<string, mixed>
     */
    private function normalize(string $method, object $response): array
    {
        $details = [
            'method' => $method,
            'type' => $response->payment_type ?? null,
            'expiry_time' => $response->expiry_time ?? null,
            'qr_url' => null,
            'qr_string' => $response->qr_string ?? null,
            'deeplink' => null,
            'va_number' => null,
            'bank' => null,
        ];

        foreach ($response->actions ?? [] as $action) {
            if ($action->name === 'generate-qr-code') {
                $details['qr_url'] = $action->url;
            }
            if ($action->name === 'deeplink-redirect') {
                $details['deeplink'] = $action->url;
            }
        }

        if (! empty($response->va_numbers)) {
            $details['va_number'] = $response->va_numbers[0]->va_number ?? null;
            $details['bank'] = strtoupper($response->va_numbers[0]->bank ?? $method);
        }

        return $details;
    }

    /**
     * Terapkan status pembayaran dari payload Midtrans ke transaksi.
     *
     * @param  array<string, mixed>  $status
     */
    public function applyStatus(array $status): ?Transaction
    {
        $orderId = $status['order_id'] ?? null;
        if (! $orderId) {
            return null;
        }

        // order_id Midtrans = payment_ref (charge Core API) atau invoice_number (transaksi lama).
        $transaction = Transaction::where('payment_ref', $orderId)
            ->orWhere('invoice_number', $orderId)
            ->first();

        if (! $transaction) {
            return null;
        }

        $trxStatus = $status['transaction_status'] ?? null;
        $fraud = $status['fraud_status'] ?? null;

        $payment = match (true) {
            in_array($trxStatus, ['capture', 'settlement'], true) && $fraud !== 'challenge' => Transaction::PAY_PAID,
            $trxStatus === 'pending' => Transaction::PAY_PENDING,
            in_array($trxStatus, ['deny', 'cancel'], true) => Transaction::PAY_FAILED,
            $trxStatus === 'expire' => Transaction::PAY_EXPIRED,
            default => $transaction->payment_status,
        };

        $data = ['payment_status' => $payment];

        if ($payment === Transaction::PAY_PAID && ! $transaction->isPaid()) {
            $data['paid'] = $transaction->total;
            $data['change'] = 0;
            $data['paid_at'] = now();
        }

        $transaction->update($data);

        return $transaction;
    }

    /**
     * Ambil status terbaru dari Midtrans lalu update transaksi (untuk polling lokal).
     */
    public function syncStatus(Transaction $transaction): Transaction
    {
        $ref = $transaction->payment_ref ?: $transaction->invoice_number;

        try {
            $status = (array) MidtransTransaction::status($ref);
        } catch (\Throwable $e) {
            return $transaction;
        }

        $this->applyStatus($status);

        return $transaction->fresh();
    }

    /**
     * Tangani notifikasi webhook Midtrans (sudah diverifikasi via status API oleh SDK).
     */
    public function handleNotification(): ?Transaction
    {
        $notification = new Notification();

        return $this->applyStatus((array) $notification->getResponse());
    }
}

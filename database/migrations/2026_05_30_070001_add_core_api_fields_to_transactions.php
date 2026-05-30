<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dukungan Midtrans Core API (charge per metode):
     * - payment_ref: order_id yang dikirim ke Midtrans untuk charge aktif (unik per percobaan).
     * - payment_details: hasil charge ternormalisasi (qr_url / va_number / deeplink / expiry) untuk UI.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_ref')->nullable()->after('payment_status');
            $table->json('payment_details')->nullable()->after('payment_ref');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['payment_ref', 'payment_details']);
        });
    }
};

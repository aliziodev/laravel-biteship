<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biteship_orders', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Polymorphic relation — links ke model order milik user (misal App\Models\Order)
            $table->morphs('orderable');

            // Biteship order identifier
            $table->string('biteship_order_id')->unique();

            // Status & tracking
            $table->string('biteship_status')->nullable();
            $table->string('waybill_id')->nullable();

            // Courier info
            $table->string('courier_company')->nullable();
            $table->string('courier_type')->nullable();
            $table->string('courier_tracking_id')->nullable();

            // Biaya — pakai unsignedBigInteger agar aman untuk nilai COD besar
            // dan compatible di MySQL maupun PostgreSQL
            $table->unsignedBigInteger('shipping_cost')->default(0);
            $table->unsignedBigInteger('insurance_cost')->default(0);
            $table->unsignedBigInteger('cod_amount')->default(0);

            // Raw response dari Biteship — dipakai untuk generate label tanpa API call
            // json() compatible dengan MySQL 5.7+ dan PostgreSQL 9.2+
            $table->json('raw_response')->nullable();

            // Timestamp milestone pengiriman — nullable, diisi saat status berubah
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('picked_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();

            // Index untuk query umum
            // morphs() sudah buat index composite (orderable_type, orderable_id)
            $table->index('biteship_status');
            $table->index('waybill_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biteship_orders');
    }
};

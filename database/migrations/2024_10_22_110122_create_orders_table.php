<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable();
            $table->string('order_code')->unique();
            $table->date('order_date');
            $table->time('order_time');
            $table->year('order_year');
            $table->enum('order_status', ['Dalam Antrian', 'Diproses', 'Selesai', 'Dibatalkan']);
            $table->enum('payment_status', ['Belum Lunas', 'Lunas']);
            $table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes('deleted_at', 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

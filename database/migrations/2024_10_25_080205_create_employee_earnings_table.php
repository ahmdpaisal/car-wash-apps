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
        Schema::create('employee_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees', 'id')->onDelete('cascade');
            $table->foreignId('order_detail_id')->constrained('order_details', 'id')->onDelete('cascade');
            $table->foreignId('order_payment_id')->constrained('order_payments', 'id')->onDelete('cascade');
            $table->integer('salary_amount');
            $table->integer('bonus_amount');
            $table->integer('total_earning');
            $table->integer('owner_share');
            $table->enum('status', ['Belum Diambil', 'Sudah Diambil'])->default('Belum Diambil');
            $table->date('earning_date');
            $table->year('earning_year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_earnings');
    }
};

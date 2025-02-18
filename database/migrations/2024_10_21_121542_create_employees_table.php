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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->enum('gender', ['Male', 'Female']);
            $table->date('birth_date');
            $table->text('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->decimal('bonus_rate', 3, 2)->default(0.05);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->foreignId('position_id')->nullable()->constrained('positions', 'id')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes('deleted_at', precision: 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

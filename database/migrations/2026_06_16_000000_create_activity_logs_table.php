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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_name')->nullable();
            $table->string('email')->nullable();
            $table->string('role')->nullable(); // Admin, Karyawan, Kurir, Pelanggan, Sistem
            $table->string('category'); // Auth & Security, Order, Payment, User Management, Finance, Payroll & Attendance, Settings & Configuration
            $table->string('activity_type'); // Login Berhasil, Login Gagal, etc.
            $table->text('description');
            $table->string('module')->nullable(); // Auth, Order, Payment, Users, Finance, Payroll, Attendance, Settings
            $table->string('reference_id')->nullable(); // Order ID, Payment ID, User ID, etc.
            $table->string('ip_address', 45)->nullable();
            $table->string('device')->nullable();
            $table->string('browser')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('data_before')->nullable();
            $table->json('data_after')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

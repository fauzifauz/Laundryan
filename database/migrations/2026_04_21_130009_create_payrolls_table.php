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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->decimal('bonus', 15, 2)->default(0);
            $table->decimal('potongan', 15, 2)->default(0);
            $table->unsignedSmallInteger('alpha_count')->default(0);
            $table->decimal('alpha_deduction', 15, 2)->default(0);
            $table->integer('month');
            $table->integer('year');
            $table->string('status')->default('pending'); // pending, paid
            $table->string('payment_method')->nullable(); // stripe, cash, bank_transfer
            $table->timestamp('payment_date')->nullable();
            $table->string('stripe_transfer_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};

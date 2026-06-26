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
            $table->string('order_code')->unique();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services');
            $table->foreignId('item_type_id')->constrained('item_types');
            $table->foreignId('courier_id')->nullable()->constrained('users');
            $table->foreignId('pickup_courier_id')->nullable()->constrained('users');
            $table->foreignId('delivery_courier_id')->nullable()->constrained('users');
            $table->text('pickup_address');
            $table->decimal('pickup_lat', 10, 8)->nullable();
            $table->decimal('pickup_lng', 11, 8)->nullable();
            $table->text('delivery_address');
            $table->decimal('delivery_lat', 10, 8)->nullable();
            $table->decimal('delivery_lng', 11, 8)->nullable();
            $table->dateTime('pickup_time');
            $table->text('notes')->nullable();
            $table->decimal('service_price', 10, 2);
            $table->decimal('item_price', 10, 2);
            $table->decimal('shipping_cost', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->string('status')->default('pending_payment');
            $table->string('payment_status')->default('pending');
            $table->string('stripe_session_id')->nullable();
            $table->string('soap')->nullable();
            $table->string('fragrance')->nullable();
            $table->string('payment_method')->default('cash');
            $table->timestamps();
        });

        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('status');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_logs');
        Schema::dropIfExists('orders');
    }
};

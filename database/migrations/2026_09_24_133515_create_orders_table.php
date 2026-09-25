<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('farmer_id')->nullable()->constrained('farmers')->onDelete('set null');
            $table->text('delivery_address')->nullable();
            $table->enum('status', ['CART', 'PENDING', 'CONFIRMED', 'READY_FOR_PICKUP', 'COMPLETED', 'CANCELLED'])->default('CART');
            $table->double('total_price')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->unique(['customer_id', 'status', 'deleted_at'], 'uk_order_customer_cart_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

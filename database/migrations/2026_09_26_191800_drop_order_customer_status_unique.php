<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('orders', 'orders_customer_id_foreign')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index('customer_id', 'orders_customer_id_foreign');
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('uk_order_customer_cart_deleted');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unique(['customer_id', 'status', 'deleted_at'], 'uk_order_customer_cart_deleted');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_customer_id_foreign');
        });
    }
};

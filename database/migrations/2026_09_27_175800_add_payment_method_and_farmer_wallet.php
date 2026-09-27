<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method', ['COD', 'BANK_TRANSFER'])->default('COD')->after('status');
        });

        Schema::create('farmer_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('farmers');
            $table->foreignId('order_id')->unique()->constrained('orders');
            $table->double('amount');
            $table->enum('type', ['credit', 'debit']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmer_wallet_transactions');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};

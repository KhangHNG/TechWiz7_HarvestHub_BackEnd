<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ward')->nullable()->after('district');
        });

        Schema::table('markets', function (Blueprint $table) {
            $table->string('city')->nullable()->after('address');
            $table->string('district')->nullable()->after('city');
            $table->string('ward')->nullable()->after('district');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('city')->nullable()->after('delivery_address');
            $table->string('district')->nullable()->after('city');
            $table->string('ward')->nullable()->after('district');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ward');
        });

        Schema::table('markets', function (Blueprint $table) {
            $table->dropColumn(['city', 'district', 'ward']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['city', 'district', 'ward']);
        });
    }
};

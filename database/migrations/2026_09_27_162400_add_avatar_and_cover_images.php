<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_url', 500)->nullable()->after('capital');
        });

        Schema::table('farmers', function (Blueprint $table) {
            $table->string('cover_url', 500)->nullable()->after('is_accepting_orders');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_url');
        });

        Schema::table('farmers', function (Blueprint $table) {
            $table->dropColumn('cover_url');
        });
    }
};

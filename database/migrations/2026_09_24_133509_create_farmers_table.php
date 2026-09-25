<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('market_id')->nullable()->constrained('markets')->onDelete('set null');
            $table->string('business_name');
            $table->text('description')->nullable();
            $table->float('rating')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->unique(['user_id', 'deleted_at'], 'uk_farmers_user_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmers');
    }
};

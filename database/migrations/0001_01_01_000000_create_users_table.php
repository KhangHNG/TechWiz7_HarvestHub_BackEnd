<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->string('password_hash');
            $table->text('address')->nullable();
            $table->enum('role', ['CUSTOMER', 'FARMER', 'ADMIN'])->default('CUSTOMER');
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->unique(['email', 'deleted_at'], 'uk_users_email_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

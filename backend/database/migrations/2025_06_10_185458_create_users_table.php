<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('user_login', 50)->unique();
            $table->string('user_password', 128);
            $table->string('user_email', 100)->unique();
            $table->boolean('user_active')->default(true);
            $table->enum('user_role', ['admin', 'member'])->default('member');
            $table->datetime('user_last_login')->nullable();
            $table->datetime('user_date_inscription')->default(\DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};

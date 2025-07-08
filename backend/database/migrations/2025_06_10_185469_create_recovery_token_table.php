<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recovery_token', function (Blueprint $table) {
            $table->id('recovery_token_id');
            $table->foreignId('recovery_token_user_id')->references('user_id')->on('user')->onDelete('cascade');
            $table->string('recovery_token_value', 255)->unique();
            $table->dateTime('recovery_token_expires_at');
            $table->boolean('recovery_token_used')->default(false);
            $table->timestamp('recovery_token_created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recovery_token');
    }
};

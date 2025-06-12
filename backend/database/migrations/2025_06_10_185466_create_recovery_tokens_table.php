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
            $table->unsignedBigInteger('recovery_token_user_id');
            $table->string('recovery_token_value', 255)->unique();
            $table->datetime('recovery_token_expires_at');
            $table->boolean('recovery_token_used')->default(false);
            $table->datetime('recovery_token_created_at')->default(\DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('recovery_token_user_id')->references('user_id')->on('user')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recovery_token');
    }
};

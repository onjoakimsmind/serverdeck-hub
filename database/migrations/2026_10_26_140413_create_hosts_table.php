<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // SHA-256 of the raw enrollment token. The raw token is shown to
            // the admin exactly once, at creation time, and never stored.
            $table->string('token_hash')->unique();
            $table->timestamp('last_seen_at')->nullable();
            $table->string('last_status')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosts');
    }
};

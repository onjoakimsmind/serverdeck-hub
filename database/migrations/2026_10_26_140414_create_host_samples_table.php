<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A short rolling history per host (see docs/design/hub.md §4.2),
        // pruned to the last 24h on every ingest. Intentionally only the
        // same small, fixed set of numbers already in the ingest payload —
        // nothing here is more sensitive than that.
        Schema::create('host_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();
            $table->timestamp('reported_at');
            $table->float('cpu_percent')->nullable();
            $table->float('memory_percent')->nullable();
            $table->float('root_disk_percent')->nullable();
            $table->unsignedInteger('docker_running')->default(0);
            $table->unsignedInteger('docker_total')->default(0);
            $table->unsignedInteger('incus_running')->default(0);
            $table->unsignedInteger('incus_total')->default(0);
            $table->unsignedInteger('checks_total')->default(0);
            $table->unsignedInteger('checks_clean')->default(0);
            $table->unsignedInteger('checks_warning')->default(0);
            $table->unsignedInteger('checks_critical')->default(0);
            $table->unsignedInteger('updates_pending')->default(0);
            $table->unsignedInteger('updates_security')->default(0);
            $table->timestamps();

            $table->index(['host_id', 'reported_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('host_samples');
    }
};

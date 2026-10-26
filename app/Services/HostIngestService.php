<?php

namespace App\Services;

use App\Models\Host;

/**
 * Stores a host's self-reported status snapshot. This is a pure write path
 * in one direction: nothing here reads back into the host, and nothing
 * here can cause the Hub to contact a host. See docs/design/hub.md §3/§7.
 */
class HostIngestService
{
    private const SAMPLE_RETENTION_HOURS = 24;

    public function ingest(Host $host, array $payload): void
    {
        $metrics = $payload['metrics'] ?? [];
        $workloads = $payload['workloads'] ?? [];
        $health = $payload['health'] ?? [];
        $updates = $payload['updates'] ?? [];

        $host->update([
            'last_seen_at' => now(),
            'last_status' => 'ok',
            'payload' => $payload,
        ]);

        $host->samples()->create([
            'reported_at' => $payload['reported_at'] ?? now(),
            'cpu_percent' => $metrics['cpu_percent'] ?? null,
            'memory_percent' => $metrics['memory_percent'] ?? null,
            'root_disk_percent' => $metrics['root_disk_percent'] ?? null,
            'docker_running' => $workloads['docker_running'] ?? 0,
            'docker_total' => $workloads['docker_total'] ?? 0,
            'incus_running' => $workloads['incus_running'] ?? 0,
            'incus_total' => $workloads['incus_total'] ?? 0,
            'checks_total' => $health['checks_total'] ?? 0,
            'checks_clean' => $health['checks_clean'] ?? 0,
            'checks_warning' => $health['checks_warning'] ?? 0,
            'checks_critical' => $health['checks_critical'] ?? 0,
            'updates_pending' => $updates['pending'] ?? 0,
            'updates_security' => $updates['security'] ?? 0,
        ]);

        $host->samples()
            ->where('reported_at', '<', now()->subHours(self::SAMPLE_RETENTION_HOURS))
            ->delete();
    }
}

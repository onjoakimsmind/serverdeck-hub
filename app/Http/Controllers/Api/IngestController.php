<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HostEnrollmentService;
use App\Services\HostIngestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The one and only write path into the Hub. A host authenticates itself
 * with a bearer token (its enrollment token) and POSTs its own status
 * snapshot. There is no corresponding read-back or command path — see
 * docs/design/hub.md §3/§7.
 */
class IngestController extends Controller
{
    public function store(Request $request, HostEnrollmentService $enrollment, HostIngestService $ingest): JsonResponse
    {
        $host = $enrollment->findByToken((string) $request->bearerToken());

        if (! $host) {
            return response()->json(['message' => 'Unknown or missing enrollment token.'], 401);
        }

        $validated = $request->validate([
            'reported_at' => ['nullable', 'date'],
            'host' => ['required', 'array'],
            'host.hostname' => ['required', 'string', 'max:255'],
            'host.os' => ['nullable', 'string', 'max:255'],
            'host.kernel' => ['nullable', 'string', 'max:255'],
            'host.uptime_seconds' => ['nullable', 'integer', 'min:0'],
            'host.serverdeck_version' => ['nullable', 'string', 'max:50'],
            'metrics' => ['nullable', 'array'],
            'metrics.cpu_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'metrics.memory_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'metrics.root_disk_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'workloads' => ['nullable', 'array'],
            'workloads.docker_running' => ['nullable', 'integer', 'min:0'],
            'workloads.docker_total' => ['nullable', 'integer', 'min:0'],
            'workloads.incus_running' => ['nullable', 'integer', 'min:0'],
            'workloads.incus_total' => ['nullable', 'integer', 'min:0'],
            'health' => ['nullable', 'array'],
            'health.checks_total' => ['nullable', 'integer', 'min:0'],
            'health.checks_clean' => ['nullable', 'integer', 'min:0'],
            'health.checks_warning' => ['nullable', 'integer', 'min:0'],
            'health.checks_critical' => ['nullable', 'integer', 'min:0'],
            'updates' => ['nullable', 'array'],
            'updates.pending' => ['nullable', 'integer', 'min:0'],
            'updates.security' => ['nullable', 'integer', 'min:0'],
            'network' => ['nullable', 'array'],
        ]);

        $ingest->ingest($host, $validated);

        return response()->json(['message' => 'ok']);
    }
}

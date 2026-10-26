<?php

namespace Tests\Feature;

use App\Models\Host;
use App\Models\HostSample;
use App\Services\HostEnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngestTest extends TestCase
{
    use RefreshDatabase;

    private function enrolledHost(): array
    {
        $generated = app(HostEnrollmentService::class)->generateToken();
        $host = Host::factory()->create(['token_hash' => $generated['hash']]);

        return [$host, $generated['token']];
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'reported_at' => now()->toIso8601String(),
            'host' => [
                'hostname' => 'web-03',
                'os' => 'Ubuntu 24.04.5 LTS',
                'kernel' => '7.0.0-38-generic',
                'uptime_seconds' => 418332,
                'serverdeck_version' => '0.1.0',
            ],
            'metrics' => ['cpu_percent' => 7.0, 'memory_percent' => 42.5, 'root_disk_percent' => 61.2],
            'workloads' => ['docker_running' => 2, 'docker_total' => 2, 'incus_running' => 0, 'incus_total' => 0],
            'health' => ['checks_total' => 12, 'checks_clean' => 9, 'checks_warning' => 2, 'checks_critical' => 0],
            'updates' => ['pending' => 7, 'security' => 2],
            'network' => ['tailscale' => ['hostname' => 'web-03.tailnet.ts.net.', 'ip' => '100.64.12.9']],
        ], $overrides);
    }

    public function test_a_valid_token_and_payload_are_accepted_and_stored(): void
    {
        [$host, $token] = $this->enrolledHost();

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/ingest', $this->payload())
            ->assertOk()
            ->assertJson(['message' => 'ok']);

        $host->refresh();

        $this->assertSame('ok', $host->last_status);
        $this->assertNotNull($host->last_seen_at);
        $this->assertSame('web-03', $host->payload['host']['hostname']);
        $this->assertSame('web-03.tailnet.ts.net.', $host->payload['network']['tailscale']['hostname']);

        $this->assertDatabaseHas('host_samples', [
            'host_id' => $host->id,
            'docker_running' => 2,
            'updates_security' => 2,
        ]);
    }

    public function test_an_unknown_token_is_rejected(): void
    {
        $this->withHeaders(['Authorization' => 'Bearer not-a-real-token'])
            ->postJson('/api/ingest', $this->payload())
            ->assertStatus(401);

        $this->assertDatabaseCount('host_samples', 0);
    }

    public function test_a_missing_token_is_rejected(): void
    {
        $this->postJson('/api/ingest', $this->payload())->assertStatus(401);
    }

    public function test_a_missing_hostname_is_rejected(): void
    {
        [, $token] = $this->enrolledHost();

        $payload = $this->payload();
        unset($payload['host']['hostname']);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/ingest', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('host.hostname');
    }

    public function test_out_of_range_percentages_are_rejected(): void
    {
        [, $token] = $this->enrolledHost();

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/ingest', $this->payload(['metrics' => ['cpu_percent' => 250]]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('metrics.cpu_percent');
    }

    public function test_samples_older_than_24_hours_are_pruned_on_ingest(): void
    {
        [$host, $token] = $this->enrolledHost();

        HostSample::factory()->for($host)->create(['reported_at' => now()->subHours(30)]);
        $recent = HostSample::factory()->for($host)->create(['reported_at' => now()->subHours(2)]);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/ingest', $this->payload())
            ->assertOk();

        $this->assertDatabaseCount('host_samples', 2); // the recent one + the one just ingested
        $this->assertDatabaseHas('host_samples', ['id' => $recent->id]);
    }

    public function test_an_ingest_never_affects_any_other_host(): void
    {
        [$hostA, $tokenA] = $this->enrolledHost();
        [$hostB] = $this->enrolledHost();

        $this->withHeaders(['Authorization' => "Bearer {$tokenA}"])
            ->postJson('/api/ingest', $this->payload())
            ->assertOk();

        $hostB->refresh();
        $this->assertNull($hostB->last_seen_at);
        $this->assertNull($hostB->payload);
    }
}

<?php

namespace Tests\Unit;

use App\Models\Host;
use App\Services\HostEnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HostEnrollmentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_tokens_are_long_and_unique(): void
    {
        $service = app(HostEnrollmentService::class);

        $first = $service->generateToken();
        $second = $service->generateToken();

        $this->assertGreaterThanOrEqual(32, strlen($first['token']));
        $this->assertNotSame($first['token'], $second['token']);
        $this->assertNotSame($first['hash'], $second['hash']);
        $this->assertSame($service->hash($first['token']), $first['hash']);
    }

    public function test_the_raw_token_is_never_stored(): void
    {
        $service = app(HostEnrollmentService::class);
        $generated = $service->generateToken();

        Host::factory()->create(['token_hash' => $generated['hash']]);

        $this->assertDatabaseMissing('hosts', ['token_hash' => $generated['token']]);
        $this->assertDatabaseHas('hosts', ['token_hash' => $generated['hash']]);
    }

    public function test_a_host_can_be_found_by_its_raw_token(): void
    {
        $service = app(HostEnrollmentService::class);
        $generated = $service->generateToken();
        $host = Host::factory()->create(['token_hash' => $generated['hash']]);

        $found = $service->findByToken($generated['token']);

        $this->assertNotNull($found);
        $this->assertSame($host->id, $found->id);
    }

    public function test_an_empty_token_never_matches(): void
    {
        $service = app(HostEnrollmentService::class);
        Host::factory()->create(['token_hash' => $service->hash('')]);

        $this->assertNull($service->findByToken(''));
    }
}

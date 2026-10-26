<?php

namespace Tests\Feature;

use App\Models\Host;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HostOnlineStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_host_that_never_reported_is_offline(): void
    {
        $host = Host::factory()->create(['last_seen_at' => null]);

        $this->assertFalse($host->isOnline());
    }

    public function test_a_host_seen_recently_is_online(): void
    {
        $host = Host::factory()->create(['last_seen_at' => now()->subMinutes(3)]);

        $this->assertTrue($host->isOnline());
    }

    public function test_a_host_not_seen_in_a_while_is_offline(): void
    {
        $host = Host::factory()->create(['last_seen_at' => now()->subMinutes(20)]);

        $this->assertFalse($host->isOnline());
    }
}

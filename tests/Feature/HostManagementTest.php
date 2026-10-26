<?php

namespace Tests\Feature;

use App\Models\Host;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HostManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_see_the_host_list(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_the_dashboard_lists_enrolled_hosts_with_their_latest_snapshot(): void
    {
        $online = Host::factory()->online()->create([
            'name' => 'web-03',
            'payload' => ['host' => ['hostname' => 'web-03'], 'metrics' => ['cpu_percent' => 7.0]],
        ]);
        $offline = Host::factory()->offline()->create(['name' => 'db-01']);

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Hosts/Index')
                ->where('hosts.0.name', 'db-01') // alphabetical
                ->where('hosts.1.name', 'web-03')
                ->where('hosts.1.online', true)
                ->where('hosts.0.online', false)
                ->etc()
            );
    }

    public function test_an_authenticated_user_can_enroll_a_host_and_see_the_token_once(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/hosts', ['name' => 'web-04'])
            ->assertRedirect()
            ->assertSessionHas('new_token');

        $this->assertDatabaseHas('hosts', ['name' => 'web-04']);

        $host = Host::query()->where('name', 'web-04')->firstOrFail();
        $this->assertNotNull($host->token_hash);
        $this->assertSame(64, strlen($host->token_hash)); // sha256 hex
    }

    public function test_a_host_name_is_required(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/hosts', ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('hosts', 0);
    }

    public function test_guests_cannot_enroll_hosts(): void
    {
        $this->post('/hosts', ['name' => 'web-05'])->assertRedirect('/login');

        $this->assertDatabaseCount('hosts', 0);
    }

    public function test_an_authenticated_user_can_remove_a_host(): void
    {
        $host = Host::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete("/hosts/{$host->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('hosts', ['id' => $host->id]);
    }
}

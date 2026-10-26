<?php

namespace Database\Factories;

use App\Models\Host;
use App\Services\HostEnrollmentService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Host>
 */
class HostFactory extends Factory
{
    protected $model = Host::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->domainWord(),
            'token_hash' => app(HostEnrollmentService::class)->hash(fake()->uuid()),
        ];
    }

    public function online(): static
    {
        return $this->state(fn () => [
            'last_seen_at' => now()->subMinutes(2),
            'last_status' => 'ok',
        ]);
    }

    public function offline(): static
    {
        return $this->state(fn () => [
            'last_seen_at' => now()->subHours(2),
            'last_status' => 'ok',
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\HostSample;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostSample>
 */
class HostSampleFactory extends Factory
{
    protected $model = HostSample::class;

    public function definition(): array
    {
        return [
            'reported_at' => now(),
            'cpu_percent' => fake()->randomFloat(1, 0, 100),
            'memory_percent' => fake()->randomFloat(1, 0, 100),
            'root_disk_percent' => fake()->randomFloat(1, 0, 100),
            'docker_running' => fake()->numberBetween(0, 5),
            'docker_total' => fake()->numberBetween(0, 5),
            'incus_running' => 0,
            'incus_total' => 0,
            'checks_total' => 10,
            'checks_clean' => 10,
            'checks_warning' => 0,
            'checks_critical' => 0,
            'updates_pending' => 0,
            'updates_security' => 0,
        ];
    }
}

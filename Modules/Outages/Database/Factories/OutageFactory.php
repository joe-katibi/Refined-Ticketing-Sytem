<?php

namespace Modules\Outages\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Outages\Models\Outage;

class OutageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Outage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $statuses = ['Reported', 'In Progress', 'Resolved', 'Closed'];
        $priorities = ['Low', 'Medium', 'High', 'Critical'];
        $impactedAreas = ['Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret'];
        $impactedServices = ['Internet', 'Voice', 'IPTV', 'VPN', 'Leased Lines'];
        
        return [
            'ticket_number' => 'OUT' . now()->format('Ymd') . $this->faker->unique()->numberBetween(1000, 9999),
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(3),
            'status' => $this->faker->randomElement($statuses),
            'priority' => $this->faker->randomElement($priorities),
            'start_time' => now()->subHours($this->faker->numberBetween(1, 72)),
            'end_time' => $this->faker->optional(0.7, null)->dateTimeBetween('-24 hours', '+24 hours'),
            'root_cause' => $this->faker->optional(0.7)->paragraph(2),
            'resolution' => $this->faker->optional(0.5)->paragraph(3),
            'impacted_areas' => $this->faker->randomElements($impactedAreas, $this->faker->numberBetween(1, 3)),
            'impacted_services' => $this->faker->randomElements($impactedServices, $this->faker->numberBetween(1, 3)),
            'assigned_team_id' => $this->faker->optional(0.8)->numberBetween(1, 5),
            'assigned_to' => $this->faker->optional(0.7)->numberBetween(1, 10),
            'reported_by' => $this->faker->numberBetween(1, 10),
            'resolved_by' => $this->faker->optional(0.5)->numberBetween(1, 10),
            'sla_breached' => $this->faker->boolean(20),
            'sla_breach_time' => $this->faker->optional(0.2)->dateTimeBetween('-24 hours', 'now'),
            'created_by' => $this->faker->numberBetween(1, 10),
            'updated_by' => $this->faker->optional(0.7)->numberBetween(1, 10),
        ];
    }

    /**
     * Configure the model factory to set specific statuses.
     *
     * @return $this
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'In Progress',
                'end_time' => null,
                'resolved_by' => null,
                'resolution' => null,
            ];
        });
    }

    public function resolved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'Resolved',
                'end_time' => now(),
                'resolved_by' => $this->faker->numberBetween(1, 10),
                'resolution' => $this->faker->paragraph(2),
            ];
        });
    }

    public function critical()
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 'Critical',
                'impacted_areas' => ['Nairobi', 'Mombasa', 'Kisumu'],
                'impacted_services' => ['Internet', 'Voice', 'VPN'],
            ];
        });
    }
}

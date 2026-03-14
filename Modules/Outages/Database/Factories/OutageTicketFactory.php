<?php

namespace Modules\Outages\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Outages\Models\OutageTicket;

class OutageTicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OutageTicket::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $statuses = ['Open', 'In Progress', 'On Hold', 'Resolved', 'Closed'];
        $priorities = ['Low', 'Medium', 'High', 'Critical'];
        $impacts = ['Low', 'Medium', 'High', 'Critical'];
        $urgencies = ['Low', 'Medium', 'High', 'Critical'];
        
        $startTime = $this->faker->dateTimeBetween('-30 days', 'now');
        $endTime = $this->faker->optional(0.7, null)
            ->dateTimeBetween($startTime, '+7 days');
            
        $isResolved = $endTime !== null && $this->faker->boolean(70);
        $status = $isResolved ? $this->faker->randomElement(['Resolved', 'Closed']) : 
                 $this->faker->randomElement(['Open', 'In Progress', 'On Hold']);
        
        return [
            'outage_id' => $this->faker->numberBetween(1, 50),
            'ticket_number' => 'TICKET-' . now()->format('Ymd') . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(3),
            'status' => $status,
            'priority' => $this->faker->randomElement($priorities),
            'impact' => $this->faker->randomElement($impacts),
            'urgency' => $this->faker->randomElement($urgencies),
            'start_time' => $startTime,
            'end_time' => $isResolved ? $endTime : null,
            'resolution' => $isResolved ? $this->faker->paragraph(2) : null,
            'resolution_notes' => $isResolved ? $this->faker->paragraph(3) : null,
            'assigned_team_id' => $this->faker->optional(0.8)->numberBetween(1, 5),
            'assigned_to' => $this->faker->optional(0.7)->numberBetween(1, 10),
            'reported_by' => $this->faker->numberBetween(1, 10),
            'sla_breached' => $this->faker->boolean(20),
            'sla_breach_time' => $this->faker->optional(0.2)->dateTimeBetween($startTime, 'now'),
            'created_by' => $this->faker->numberBetween(1, 10),
            'updated_by' => $this->faker->optional(0.7)->numberBetween(1, 10),
        ];
    }

    /**
     * Configure the model factory to set specific statuses.
     *
     * @return $this
     */
    public function open()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'Open',
                'end_time' => null,
                'resolution' => null,
                'resolution_notes' => null,
            ];
        });
    }

    public function inProgress()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'In Progress',
                'end_time' => null,
                'resolution' => null,
                'resolution_notes' => null,
            ];
        });
    }

    public function resolved()
    {
        return $this->state(function (array $attributes) {
            $endTime = $this->faker->dateTimeBetween('-7 days', 'now');
            
            return [
                'status' => 'Resolved',
                'end_time' => $endTime,
                'resolution' => $this->faker->paragraph(2),
                'resolution_notes' => $this->faker->paragraph(3),
            ];
        });
    }

    public function critical()
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 'Critical',
                'impact' => 'Critical',
                'urgency' => 'Critical',
            ];
        });
    }
}

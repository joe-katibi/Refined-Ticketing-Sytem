<?php

namespace Modules\Outages\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Outages\Models\OutageProgress;

class OutageProgressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OutageProgress::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $statuses = [
            'Reported', 'In Progress', 'On Hold', 'Resolved', 'Closed',
            'Awaiting Customer', 'Awaiting Vendor', 'Pending', 'Reopened'
        ];
        
        $isForTicket = $this->faker->boolean(50);
        $isMajorUpdate = $this->faker->boolean(30);
        
        return [
            'outage_id' => $isForTicket ? null : $this->faker->numberBetween(1, 50),
            'ticket_id' => $isForTicket ? $this->faker->numberBetween(1, 100) : null,
            'user_id' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->randomElement($statuses),
            'notes' => $this->faker->paragraph(3),
            'action_taken' => $this->faker->optional(0.8)->paragraph(2),
            'next_steps' => $this->faker->optional(0.6)->paragraph(2),
            'is_major_update' => $isMajorUpdate,
            'created_by' => $this->faker->numberBetween(1, 10),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Configure the model factory to set specific states.
     *
     * @return $this
     */
    public function forOutage($outageId)
    {
        return $this->state(function (array $attributes) use ($outageId) {
            return [
                'outage_id' => $outageId,
                'ticket_id' => null,
            ];
        });
    }

    public function forTicket($ticketId)
    {
        return $this->state(function (array $attributes) use ($ticketId) {
            return [
                'outage_id' => null,
                'ticket_id' => $ticketId,
            ];
        });
    }

    public function majorUpdate()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_major_update' => true,
                'action_taken' => $this->faker->paragraph(3),
                'next_steps' => $this->faker->paragraph(2),
            ];
        });
    }

    public function statusUpdate($status)
    {
        return $this->state(function (array $attributes) use ($status) {
            return [
                'status' => $status,
                'is_major_update' => true,
                'notes' => "Status changed to {$status}",
            ];
        });
    }

    public function withActionTaken()
    {
        return $this->state(function (array $attributes) {
            return [
                'action_taken' => $this->faker->paragraph(3),
                'next_steps' => $this->faker->optional(0.7)->paragraph(2),
            ];
        });
    }
}

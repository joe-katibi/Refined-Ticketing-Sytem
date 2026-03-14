<?php

namespace Modules\Outages\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Outages\Models\OutageReason;

class OutageReasonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OutageReason::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $reasonTypes = [
            'Network', 'Hardware', 'Software', 'Power', 'Human Error', 
            'Scheduled Maintenance', 'External Provider', 'Security', 'Other'
        ];
        
        $isResolved = $this->faker->boolean(70);
        
        return [
            'outage_id' => $this->faker->numberBetween(1, 50),
            'ticket_id' => $this->faker->optional(0.5)->numberBetween(1, 100),
            'reason_type' => $this->faker->randomElement($reasonTypes),
            'description' => $this->faker->sentence(10),
            'root_cause' => $this->faker->optional(0.8)->paragraph(2),
            'resolution' => $isResolved ? $this->faker->paragraph(2) : null,
            'resolved_by' => $isResolved ? $this->faker->numberBetween(1, 10) : null,
            'resolved_at' => $isResolved ? $this->faker->dateTimeBetween('-30 days', 'now') : null,
            'created_by' => $this->faker->numberBetween(1, 10),
            'updated_by' => $this->faker->optional(0.7)->numberBetween(1, 10),
        ];
    }

    /**
     * Configure the model factory to set specific states.
     *
     * @return $this
     */
    public function resolved()
    {
        return $this->state(function (array $attributes) {
            return [
                'resolved_by' => $this->faker->numberBetween(1, 10),
                'resolved_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
                'resolution' => $this->faker->paragraph(2),
            ];
        });
    }

    public function unresolved()
    {
        return $this->state(function (array $attributes) {
            return [
                'resolved_by' => null,
                'resolved_at' => null,
                'resolution' => null,
            ];
        });
    }

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
}

<?php

namespace Modules\Outages\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Outages\Models\OutageAttachment;

class OutageAttachmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OutageAttachment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $fileTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'txt' => 'text/plain',
            'log' => 'text/plain',
        ];
        
        $ext = $this->faker->randomElement(array_keys($fileTypes));
        $fileName = Str::random(10) . '.' . $ext;
        $fileType = $fileTypes[$ext];
        $fileSize = $this->faker->numberBetween(1000, 10485760); // 1KB to 10MB
        
        $isForTicket = $this->faker->boolean(50);
        
        return [
            'outage_id' => $isForTicket ? null : $this->faker->numberBetween(1, 50),
            'ticket_id' => $isForTicket ? $this->faker->numberBetween(1, 100) : null,
            'user_id' => $this->faker->numberBetween(1, 10),
            'file_name' => $fileName,
            'file_path' => 'attachments/' . $fileName,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'description' => $this->faker->optional(0.7)->sentence(),
            'uploaded_by' => $this->faker->numberBetween(1, 10),
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

    public function image()
    {
        $ext = $this->faker->randomElement(['jpg', 'jpeg', 'png']);
        $fileName = Str::random(10) . '.' . $ext;
        
        return $this->state(function (array $attributes) use ($fileName, $ext) {
            return [
                'file_name' => $fileName,
                'file_path' => 'attachments/' . $fileName,
                'file_type' => 'image/' . $ext,
                'file_size' => $this->faker->numberBetween(1000, 1048576), // 1KB to 1MB
            ];
        });
    }

    public function document()
    {
        $ext = $this->faker->randomElement(['pdf', 'doc', 'docx']);
        $fileName = Str::random(10) . '.' . $ext;
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        
        return $this->state(function (array $attributes) use ($fileName, $ext, $mimeTypes) {
            return [
                'file_name' => $fileName,
                'file_path' => 'attachments/' . $fileName,
                'file_type' => $mimeTypes[$ext],
                'file_size' => $this->faker->numberBetween(1000, 10485760), // 1KB to 10MB
            ];
        });
    }
}

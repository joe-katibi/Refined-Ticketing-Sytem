<?php

namespace Modules\Appointment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Appointment\Database\Seeders\AppointmentStatusSeeder;

class AppointmentDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            AppointmentStatusSeeder::class,
        ]);
    }
}

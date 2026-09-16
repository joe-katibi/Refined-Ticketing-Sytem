<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
      // Previously this only seeded permissions/roles/departments/users, leaving
      // every lookup table a ticket-creation form depends on (appointment types,
      // sub-departments, team types, escalation categories, outage affected
      // areas/services/final-reasons) completely empty on a fresh install — none
      // of the Create Appointment / Create Outage / Create Escalation forms could
      // be submitted until an admin manually populated ~10 settings pages first.
      $this->call([
        PermissionsSeeder::class,
        RolesSeeder::class,
        DepartmentSeeder::class,
        SubDepartmentSeeder::class,
        TeamTypeSeeder::class,
        RegionSeeder::class,
        UsersSeeder::class,
        \Modules\Appointment\Database\Seeders\AppointmentDatabaseSeeder::class,
        \Modules\Escalations\Database\Seeders\EscalationsDatabaseSeeder::class,
        \Modules\Outages\Database\Seeders\AffectedAreaSeeder::class,
        \Modules\Outages\Database\Seeders\AffectedServiceSeeder::class,
        \Modules\Outages\Database\Seeders\OutageFinalReasonSeeder::class,
      ]);
    }
}

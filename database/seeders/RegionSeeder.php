<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;

/**
 * Region names taken from the OLT/coverage areas that actually appear in the
 * historical ticket export (OLT column), not placeholders.
 */
class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            'Nsambya', 'Kabowa', 'Namirembe', 'Naguru', 'Salaama', 'Kireka',
            'Bahai', 'Naalya', 'Nakulabye', 'Kyanja', 'Kibiri', 'Seguku',
            'Kira', 'Raxio', 'Kitende', 'Gangu', 'Nansana', 'Naluvule', 'Kisasi',
        ];

        foreach ($regions as $name) {
            Region::firstOrCreate(['name' => $name], ['status' => 'Active']);
        }
    }
}

<?php

namespace Modules\Escalations\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Escalations\Entities\Category;
use Modules\Escalations\Entities\Subcategory;
use Modules\Escalations\Entities\Source;

/**
 * Seeds baseline Escalation categories/subcategories/sources. Previously
 * EscalationsDatabaseSeeder::run() was empty (`$this->call([])`), so on a
 * fresh install the Category dropdown on the Create Escalation form was
 * empty and the form (Category is a required field) could not be submitted
 * at all.
 */
class EscalationCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Network Issue' => ['LOS', 'Slow Speed', 'Intermittent Connection', 'Equipment Issue'],
            'Billing Issue' => ['Incorrect Charge', 'Payment Not Reflected'],
            'Service Request' => ['Upgrade Request', 'Downgrade Request'],
            'Customer Complaint' => ['Service Quality', 'Staff Conduct'],
        ];

        foreach ($categories as $categoryName => $subcategoryNames) {
            $category = Category::firstOrCreate(
                ['category_name' => $categoryName],
                ['status' => 'Active']
            );

            foreach ($subcategoryNames as $subcategoryName) {
                Subcategory::firstOrCreate(
                    ['sub_category_name' => $subcategoryName, 'category_id' => $category->id],
                    ['status' => 'Active']
                );
            }
        }

        foreach (['Phone Call', 'Email', 'Walk-in', 'Social Media', 'Customer Portal'] as $sourceName) {
            Source::firstOrCreate(['name' => $sourceName]);
        }
    }
}

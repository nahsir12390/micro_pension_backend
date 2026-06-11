<?php

namespace Database\Seeders;

use App\Models\PensionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@micropension.test'],
            [
                'name' => 'System Admin',
                'phone' => '08000000000',
                'occupation' => 'Administrator',
                'location' => 'Lagos, Nigeria',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        PensionPlan::updateOrCreate(
            ['name' => 'Daily Plan'],
            ['description' => 'For informal workers who prefer daily savings.', 'minimum_amount' => 500, 'frequency' => 'daily', 'is_active' => true]
        );

        PensionPlan::updateOrCreate(
            ['name' => 'Weekly Plan'],
            ['description' => 'Flexible weekly savings for steady income earners.', 'minimum_amount' => 3000, 'frequency' => 'weekly', 'is_active' => true]
        );

        PensionPlan::updateOrCreate(
            ['name' => 'Monthly Plan'],
            ['description' => 'Monthly contribution plan for bigger savings.', 'minimum_amount' => 10000, 'frequency' => 'monthly', 'is_active' => true]
        );

        $admin->save();
    }
}

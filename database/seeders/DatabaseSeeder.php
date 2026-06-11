<?php

namespace Database\Seeders;

use App\Models\Contribution;
use App\Models\PensionPlan;
use App\Models\User;
use App\Models\Withdrawal;
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

        $worker = User::updateOrCreate(
            ['email' => 'worker@micropension.test'],
            [
                'name' => 'Amina Yusuf',
                'phone' => '08012345678',
                'occupation' => 'Trader',
                'location' => 'Lagos, Nigeria',
                'role' => 'worker',
                'password' => Hash::make('password'),
            ]
        );

        $daily = PensionPlan::updateOrCreate(
            ['name' => 'Daily Plan'],
            ['description' => 'For informal workers who prefer daily savings.', 'minimum_amount' => 500, 'frequency' => 'daily', 'is_active' => true]
        );

        $weekly = PensionPlan::updateOrCreate(
            ['name' => 'Weekly Plan'],
            ['description' => 'Flexible weekly savings for steady income earners.', 'minimum_amount' => 3000, 'frequency' => 'weekly', 'is_active' => true]
        );

        PensionPlan::updateOrCreate(
            ['name' => 'Monthly Plan'],
            ['description' => 'Monthly contribution plan for bigger savings.', 'minimum_amount' => 10000, 'frequency' => 'monthly', 'is_active' => true]
        );

        Contribution::updateOrCreate(
            ['reference' => 'DEMO-CON-001'],
            ['user_id' => $worker->id, 'pension_plan_id' => $weekly->id, 'amount' => 3000, 'payment_method' => 'Bank Transfer', 'status' => 'successful', 'contributed_at' => now()]
        );

        Contribution::updateOrCreate(
            ['reference' => 'DEMO-CON-002'],
            ['user_id' => $worker->id, 'pension_plan_id' => $daily->id, 'amount' => 1000, 'payment_method' => 'USSD', 'status' => 'successful', 'contributed_at' => now()->subDay()]
        );

        Withdrawal::updateOrCreate(
            ['user_id' => $worker->id, 'amount' => 15000, 'reason' => 'Emergency withdrawal'],
            ['bank_name' => 'Demo Bank', 'account_number' => '0123456789', 'account_name' => 'Amina Yusuf', 'status' => 'pending']
        );

        $admin->save();
    }
}

<?php

namespace Database\Seeders;

use App\Enums\SubscriptionEnum;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Monthly plan
        SubscriptionPlan::firstOrCreate(
            ['slug' => 'monthly'],
            [
                'name' => 'Monthly',
                'price' => SubscriptionEnum::MONTHLY_PRICE->value,
                'currency' => SubscriptionEnum::currency(),
                'duration_days' => SubscriptionEnum::MONTHLY_DURATION_DAYS->value,
                'is_active' => true,
            ]
        );

        // Yearly plan
        SubscriptionPlan::firstOrCreate(
            ['slug' => 'yearly'],
            [
                'name' => 'Yearly',
                'price' => SubscriptionEnum::YEARLY_PRICE->value,
                'currency' => SubscriptionEnum::currency(),
                'duration_days' => SubscriptionEnum::YEARLY_DURATION_DAYS->value,
                'is_active' => true,
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\Hook;
use Illuminate\Database\Seeder;

class HookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hooks = [
            'Question',
            'Action',
            'Visual',
            'Emotional',
            'Mystery',
            'Sound',
        ];

        foreach ($hooks as $hookName) {
            Hook::firstOrCreate(['name' => $hookName]);
        }
    }
}

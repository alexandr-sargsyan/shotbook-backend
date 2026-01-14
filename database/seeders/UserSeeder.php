<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Создает дефолтного пользователя для разработчиков.
     * Учетные данные:
     * - Email: developer@example.com
     * - Password: developer
     * - Email подтвержден
     */
    public function run(): void
    {
        // Проверяем, существует ли уже дефолтный пользователь
        $existingUser = User::where('email', 'developer@example.com')->first();
        
        if ($existingUser) {
            $this->command->info('Дефолтный пользователь уже существует. Пропускаем создание.');
            return;
        }

        // Создаем дефолтного пользователя
        $user = User::create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => Hash::make('developer'),
            'email_verified_at' => now(), // Email сразу подтвержден для удобства разработки
        ]);

        // Создаем дефолтный каталог "Избранное"
        \App\Models\VideoCollection::create([
            'user_id' => $user->id,
            'name' => 'Избранное',
            'is_default' => true,
        ]);

        // Добавляем роль администратора
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $user->roles()->attach($adminRole->id);
            $this->command->info('  Роль администратора добавлена');
        }

        $this->command->info('Дефолтный пользователь создан:');
        $this->command->info('  Email: developer@example.com');
        $this->command->info('  Password: developer');
        $this->command->info('  Email подтвержден: Да');
        $this->command->info('  Дефолтный каталог "Избранное" создан');
        $this->command->info('  Роль: Администратор');
    }
}


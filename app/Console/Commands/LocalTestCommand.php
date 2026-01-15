<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LocalTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'local:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Локальное тестирование различных функций приложения';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧪 Локальное тестирование приложения');
        $this->newLine();

        // Здесь можно добавлять любые тесты
        $this->info('Начало тестирования...');
        Log::info('Local test command started');

        // Пример простого теста
        $this->line('✓ Тест выполнен успешно');
        Log::info('Local test completed successfully');

        $this->newLine();
        $this->info('✅ Тестирование завершено');

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

use App\Jobs\ProcessWebhookJob;
use App\Jobs\ProcessProductQueueJob;
use App\Jobs\ProcessProductImageGenerateJob;
use App\Jobs\QueueJob;
use App\Models\Product;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new ProcessProductQueueJob())
            ->everyTwoHours()
            // ->everyFifteenMinutes()
            // ->cron('0,45 * * * *')
            ->between('08:00', '21:00');

        $schedule->job(new ProcessProductImageGenerateJob())
            ->everyThreeHours()
            // ->everyMinute()
            ->between('08:00', '21:00');

        // Inicia Fila de Envios em Segundo-Plano
        $schedule->job(new QueueJob())->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Jobs\ProcessWebhookJob;
use App\Jobs\QueueJob;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Processamento dos WebHooks Pendentes (table: webhooks where status = 23)
        $schedule->job(new ProcessWebhookJob())->everyMinute();


        
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

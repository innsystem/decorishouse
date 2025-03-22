<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

use App\Jobs\ProcessWebhookJob;
use App\Jobs\QueueJob;
use App\Jobs\GenerateProductImageJob;
use App\Models\Product;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->call(function () {
        //     // Busca um produto que ainda não teve a imagem gerada
        //     $product = Product::whereDoesntHave('generatedImages')->inRandomOrder()->first();

        //     if ($product) {
        //         dispatch(new GenerateProductImageJob($product));
        //         Log::info("Job de geração de imagem disparada para o produto ID: " . $product->id . " " . $product->name);
        //     }
        // })->everyFiveMinutes()->between('09:00', '19:00')->name('generate_product_image')->withoutOverlapping();

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

<?php

namespace App\Jobs;

use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {}

    public function handle(WebhookService $webhookService)
    {
        \Log::info('Init ProcessWebhookJob');
        
        $pendingWebhooks = Webhook::where('status', 23)->get(); // 23 = Pendente
        foreach ($pendingWebhooks as $webhook) {
            $webhookService->checkPaymentStatus($webhook);
        }
    }
}

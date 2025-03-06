<?php

namespace App\Services;

use App\Integrations\MercadoPagoIntegration;
use App\Jobs\ProcessNotificationJob;
use App\Models\Webhook;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    protected $transactionService;
    protected $paymentIntegrations;

    public function __construct(TransactionService $transactionService)
    {
        // Aqui podemos registrar múltiplas integrações dinamicamente
        $this->paymentIntegrations = [
            'mercadopago' => new MercadoPagoIntegration(),
            // 'paypal' => new PayPalService(),
            // 'stripe' => new StripeService(),
        ];

        $this->transactionService = $transactionService;
    }

    public function createWebhook($data)
    {
        return Webhook::create($data);
    }

    public function checkPaymentStatus(Webhook $webhook)
    {
        $integration = $webhook->integration->slug; // Exemplo: 'mercadopago'

        if (!isset($this->paymentIntegrations[$integration])) {
            Log::error("Integração {$integration} não implementada!");
            return;
        }

        // Inicia o service de Integração com Meio de Pagamento
        $service = $this->paymentIntegrations[$integration];

        // Usa a funcao de getOrder para obter informações do pagamento
        $getPayment = $service->getOrder($webhook->payment_code);

        if (!$getPayment) {
            Log::error("Erro ao obter status do pagamento para o webhook ID #{$webhook->id}");
            return;
        }

        // Traduz a mensagem de resposta do Pagamento
        $paymentResult = $service->handlePaymentResponse($getPayment, $webhook->invoice);

        // ATENÇÃO - ATENÇÃO - ATENÇÃO
        // $paymentResult['status'] = 'paid';

        if (isset($paymentResult) && isset($paymentResult['status'])) {
            if ($paymentResult['status'] === 'paid' || $paymentResult['status'] === 'approved') {
                $this->confirmPayment($webhook);
            } elseif ($paymentResult['status'] === 'pending') {
            } else {
                $webhook->status = 26;
                $webhook->response_json = $paymentResult['response_json'];
                $webhook->save();
            }
        } else {
            $webhook->response_json = $paymentResult['response_json'];
            $webhook->save();
        }
    }

    protected function confirmPayment(Webhook $webhook)
    {
        $invoice = Invoice::find($webhook->invoice_id);
        if (!$invoice) return;

        $invoice->status = 24; // Pago
        $invoice->paid_at = now();
        $invoice->save();

        $webhook->status = 24; // Pago
        $webhook->save();

        // Aqui dentro precisará ter uma função que irá identificar
        // se existe itens na fatura que deverão ter seu registro confirmado, aprovado e renovado

        // Recupera a integração vinculada ao pagamento
        $integration = $invoice->integration;

        // Se houver integração, calcula a taxa do gateway
        $gatewayFee = 0;
        if ($integration) {
            // Define dinamicamente o nome do campo de taxa com base no método de pagamento
            $feeKey = 'fee_' . $invoice->method_type; // Exemplo: 'fee_pix', 'fee_boleto', 'fee_credit_card'

            // Se a taxa existir na configuração, calcula a taxa do gateway
            if (isset($integration->settings[$feeKey])) {
                $percentage = floatval($integration->settings[$feeKey]); // Exemplo: 2.99 para 2.99%
                $gatewayFee = ($invoice->total * $percentage) / 100;
            }
        }

        $data = [
            'invoice_id' => $invoice->id,
            'integration_id' => $invoice->integration_id,
            'type' => 'income',
            'amount' => $invoice->total,
            'gateway_fee' => $gatewayFee,
            'description' => "Pagamento confirmado para a fatura #{$invoice->id}",
            'date' => now(),
        ];

        $this->transactionService->createTransaction($data);

        $notificationData = [
            'name' => $invoice->user->name,
            'invoice_id' => $invoice->id,
		];

		dispatch(new ProcessNotificationJob('email', $invoice->user->email, 'Invoice', 'email', 'payment_confirmed', $notificationData));
		if(isset($invoice->user->phone) && !empty($invoice->user->phone)) {
			dispatch(new ProcessNotificationJob('whatsapp', $invoice->user->phone, 'Invoice', 'whatsapp', 'payment_confirmed', $notificationData));
		}
    }
}

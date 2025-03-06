<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\Status;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use App\Services\InvoiceService;
use App\Services\WebhookService;
use App\Integrations\MercadoPagoIntegration;


class InvoicesController extends Controller
{
    public $name = 'Fatura'; //  singular
    public $folder = 'admin.pages.invoices';

    protected $invoiceService;
    protected $webhookService;
    protected $mercadoPagoIntegration;

    public function __construct(InvoiceService $invoiceService, WebhookService $webhookService, MercadoPagoIntegration $mercadoPagoIntegration)
    {
        $this->invoiceService = $invoiceService;
        $this->webhookService = $webhookService;
        $this->mercadoPagoIntegration = $mercadoPagoIntegration;
    }

    public function index()
    {
        return view($this->folder . '.index');
    }

    public function load(Request $request)
    {
        $query = [];
        $filters = $request->only(['name', 'invoice_id', 'status', 'date_range']);

        $data = $this->invoiceService->getAllInvoices($filters);

        return view($this->folder . '.index_load', $data);
    }

    public function create()
    {
        $statuses = Status::forInvoices();
        $users = User::all();
        $integrations = Integration::where('type', 'payments')->get();

        return view($this->folder . '.form', compact('statuses', 'users', 'integrations'));
    }

    public function store(Request $request)
    {
        $result = $request->all();

        $rules = [
            'user_id' => 'required|exists:users,id',
            'integration_id' => 'required|exists:integrations,id',
            'method_type' => 'nullable|in:pix,boleto,credit_card',
            'status' => 'required|integer',
            'due_at' => 'required',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price_unit' => 'required|numeric|min:0',
            'items.*.price_total' => 'required|numeric|min:0',
        ];

        $messages = [
            'user_id.required' => 'O cliente é obrigatório.',
            'integration_id.required' => 'A integração de pagamento é obrigatória.',
            'due_at.required' => 'A data de vencimento é obrigatória.',
            'items.required' => 'A fatura precisa ter pelo menos um item.',
        ];

        $validator = Validator::make($result, $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 422);
        }

        $invoice = $this->invoiceService->createInvoice($result);

        return response()->json($this->name . ' adicionada com sucesso', 200);
    }

    public function show($id)
    {
        $result = $this->invoiceService->getInvoiceById($id);

        if (isset($result->latestWebhook)) {
            $webhookCheckStatus = $this->webhookService->checkPaymentStatus($result->latestWebhook);
        }

        $settingsMercadoPago = $this->mercadoPagoIntegration->getSettings();

        return view($this->folder . '.show', compact('result', 'settingsMercadoPago'));
    }

    public function edit($id)
    {
        $result = $this->invoiceService->getInvoiceById($id);
        $statuses = Status::forInvoices();
        $users = User::all();
        $integrations = Integration::where('type', 'payments')->get();

        return view($this->folder . '.form', compact('result', 'statuses', 'users', 'integrations'));
    }

    public function update(Request $request, $id)
    {
        $result = $request->all();

        $rules = [
            'integration_id' => 'required|exists:integrations,id',
            'method_type' => 'nullable|in:pix,boleto,credit_card',
            'status' => 'required|integer',
            'due_at' => 'required',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price_unit' => 'required|numeric|min:0',
            'items.*.price_total' => 'required|numeric|min:0',
        ];

        $messages = [
            'integration_id.required' => 'A integração de pagamento é obrigatória.',
            'due_at.required' => 'A data de vencimento é obrigatória.',
            'items.required' => 'A fatura precisa ter pelo menos um item.',
        ];

        $validator = Validator::make($result, $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 422);
        }

        $invoice = $this->invoiceService->updateInvoice($id, $result);

        return response()->json($this->name . ' atualizada com sucesso', 200);
    }

    public function delete($id)
    {
        $this->invoiceService->deleteInvoice($id);

        return response()->json($this->name . ' excluída com sucesso', 200);
    }

    public function cancel($id)
    {
        $this->invoiceService->cancelInvoice($id);

        return response()->json($this->name . ' cancelada com sucesso', 200);
    }

    public function confirmPayment(Request $request, $id)
    {
        $notifyClient = $request->input('notify', 0);
        $this->invoiceService->confirmPayment($id, $notifyClient);

        return response()->json('Pagamento confirmado com sucesso', 200);
    }

    public function sendReminder($id)
    {
        $this->invoiceService->sendReminder($id);

        return response()->json('Lembrete enviado com sucesso', 200);
    }

    public function generatePayment(Request $request, $id)
    {
        $result = $request->all();

        $invoice = $this->invoiceService->getInvoiceById($id);
        $paymentMethod = $request->input('payment_method');

        if (!in_array($paymentMethod, ['pix', 'boleto', 'credit_card'])) {
            return response()->json('Método de pagamento inválido.', 422);
        }

        // Updated the invoice with the payment method
        $invoice->method_type = $paymentMethod;
        $invoice->save();

        $items = [];
        $transaction_amount = 0;

        foreach ($invoice->items as $item_row) {
            $items[] = [
                'id' => $item_row['id'],
                'title' => $item_row['description'],
                'description' => 'Detalhes descritos ' . $item_row['description'],
                'quantity' => (int) $item_row['quantity'],
                'unit_price' => (float) $item_row['price_unit'],
                'category_id' => 'others',
            ];

            $transaction_amount += (int) $item_row['quantity'] * (float) $item_row['price_unit'];
        }

        if ($paymentMethod == 'pix') {
            $createRequest = [
                "additional_info" => [
                    "items" => $items,
                    "payer" => [
                        "first_name" => $invoice->user->name,
                        "last_name" => "Cliente",
                        "phone" => [
                            "area_code" => 16,
                            "number" => "992747526"
                        ],
                        "address" => [
                            "street_number" => null,
                        ],
                    ],
                    "shipments" => [
                        "receiver_address" => [
                            "zip_code" => "14079125",
                            "state_name" => "São Paulo",
                            "city_name" => "Ribeirão Preto",
                            "street_name" => "Rua Vicente Barilari Sobrinho",
                            "street_number" => 325
                        ]
                    ],
                ],
                "description" => "Pagamento da Fatura #" . $invoice->id,
                "external_reference" => "invoice." . $invoice->id,
                "installments" => 1,
                "payer" => [
                    "entity_type" => "individual",
                    "type" => "customer",
                    "email" => $invoice->user->email,
                    "identification" => [
                        "type" => strlen($this->formatDocument($invoice->user->document)) < 13 ? 'CPF' : 'CNPJ',
                        "number" => $this->formatDocument($invoice->user->document)
                    ]
                ],
                "payment_method_id" => 'pix',
                "transaction_amount" => (float) $transaction_amount,
                "notification_url" => 'https://integrations.innsystem.com.br/mercadopago/webhook', // URL de notificação
            ];
        } else if ($paymentMethod == 'boleto') {
            $createRequest = [
                "additional_info" => [
                    "items" => $items,
                    "payer" => [
                        "first_name" => $invoice->user->name,
                        "last_name" => "Cliente",
                        "phone" => [
                            "area_code" => 16,
                            "number" => "992747526"
                        ],
                        "address" => [
                            "street_number" => null,
                        ],
                    ],
                    "shipments" => [
                        "receiver_address" => [
                            "zip_code" => "14079125",
                            "state_name" => "São Paulo",
                            "city_name" => "Ribeirão Preto",
                            "street_name" => "Rua Vicente Barilari Sobrinho",
                            "street_number" => 325
                        ]
                    ],
                ],
                "description" => "Pagamento da Fatura #" . $invoice->id,
                "external_reference" => "invoice." . $invoice->id,
                "installments" => 1,
                "payer" => [
                    "entity_type" => "individual",
                    "type" => "customer",
                    "email" => $invoice->user->email,
                    "identification" => [
                        "type" => strlen($this->formatDocument($invoice->user->document)) < 13 ? 'CPF' : 'CNPJ',
                        "number" => $this->formatDocument($invoice->user->document)
                    ]
                ],
                "payment_method_id" => 'ted',
                "transaction_amount" => (float) $transaction_amount,
                "notification_url" => 'https://integrations.innsystem.com.br/mercadopago/webhook', // URL de notificação
            ];
        } else {
            $result = $request->all();

            $rules = [
                'name_holder' => 'required',
                'document_holder' => 'required',
                'card_number' => 'required',
                'expiration_month' => 'required',
                'expiration_year' => 'required',
                'security_code' => 'required',
            ];

            $messages = [
                'name_holder.required' => 'O nome é obrigatório.',
                'document_holder.required' => 'O documento é obrigatório.',
                'card_number.required' => 'O número do cartão é obrigatório.',
                'expiration_month.required' => 'A data de expiração é obrigatório.',
                'expiration_year.required' => 'O ano de expiração é obrigatório.',
                'security_code.required' => 'O código de segurança é obrigatório.',
            ];

            $validator = Validator::make($result, $rules, $messages);

            if ($validator->fails()) {
                return response()->json($validator->errors()->first(), 422);
            }

            // Atualiza o Documento do Cliente
            $invoice->user->document = $this->formatDocument($invoice->user->document);
            $invoice->user->save();

            // Cria Cliente
            $user_data = [
                'first_name' => $invoice->user->name,
                'email' => $invoice->user->email,
            ];

            $customer_id = null;
            $user_create = $this->mercadoPagoIntegration->createCustomer($user_data);

            if (isset($user_create['error'])) {                
                if (isset($user_create['message']['cause']) && $user_create['message']['cause'][0]['code'] == '101') {
                    $getCustomer = $this->mercadoPagoIntegration->searchCustomer($user_data['email']);
                    foreach ($getCustomer['results'] as $customer) {
                        $customer_id = $customer['id'];
                    }
                } else {
                    return response()->json($user_create['message'], 422);
                }
            }

            // Tokeniza Cartão
            $token_card = null;
            $bin = substr(preg_replace('/\D/', '', $result['card_number']), 0, 6);

            $user_card = [
                'card_number' => $this->formatDocument($result['card_number']),
                'expiration_month' => $result['expiration_month'],
                'expiration_year' => $result['expiration_year'],
                'security_code' => $result['security_code'],
                'cardholder' => [
                    'name' => $result['name_holder'],
                    'identification' => [
                        'type' => strlen($this->formatDocument($invoice->user->document)) < 13 ? 'CPF' : 'CNPJ',
                        'number' => $this->formatDocument($invoice->user->document),
                    ],
                ],
                'bin' => $bin,
            ];

            $generateCard = $this->mercadoPagoIntegration->generateCardToken($user_card);

            if (isset($generateCard['error'])) {
                if(isset($generateCard['message']['cause'])){
                    return response()->json($generateCard['message']['cause'][0]['description'], 422);
                }else{
                    return response()->json($generateCard['message']['message'], 422);
                }
            }

            $token_card = $generateCard['id'];

            // Adiciona o cartão ao cliente
            $card_related = [
                'customer_id' => $customer_id,
                'token' => $token_card,
            ];

            $this->mercadoPagoIntegration->addCardCustomer($card_related);

            $createRequest = [
                "additional_info" => [
                    "items" => $items,
                    "payer" => [
                        "first_name" => $invoice->user->name,
                        "last_name" => "Cliente",
                    ],
                ],
                "description" => "Pagamento da Fatura #" . $invoice->id,
                "external_reference" => "invoice." . $invoice->id,
                "installments" => (float) $result['installments'] ?? 1,
                "payer" => [
                    "entity_type" => "individual",
                    "type" => "customer",
                    "email" => $invoice->user->email,
                    "identification" => [
                        "type" => strlen($this->formatDocument($invoice->user->document)) < 13 ? 'CPF' : 'CNPJ',
                        "number" => $this->formatDocument($invoice->user->document)
                    ]
                ],
                "statement_descriptor" => 'INNSYSTEM',
                "payment_method_id" => 'master',
                "token" => $token_card, // Token do Cartão
                "transaction_amount" => (float) $transaction_amount,
                "notification_url" => 'https://integrations.innsystem.com.br/mercadopago/webhook', // URL de notificação
            ];
        }

        // dd($createRequest);

        try {
            $response = $this->mercadoPagoIntegration->createPayment($createRequest);

            if (isset($response['error'])) {
                return response()->json($response['message']['cause'][0]['description'], 422);
            }

            $paymentResult = $this->mercadoPagoIntegration->handlePaymentResponse($response, $invoice);

            $webhookData = [
                'invoice_id' => $invoice->id,
                'integration_id' => $invoice->integration_id,
                'payment_code' => $response['id'],
                'status' => ($paymentResult['status'] === 'rejected') ? 26 : 23, // Cancelado / Pendente
                'response_json' => $paymentResult['response_json'],
            ];

            $this->webhookService->createWebhook($webhookData);

            return response()->json([
                'status' => $paymentResult['status'],
                'message' => $paymentResult['message'],
                'status_http' => $paymentResult['http_code'],
                'payment_method' => $paymentMethod,
            ], $paymentResult['http_code']);
        } catch (\Exception $e) {
            return response()->json('Erro ao processar pagamento: ' . $e->getMessage());
        }
    }

    public function loadInstallments(Request $request, $id)
    {
        $result = $this->invoiceService->getInvoiceById($id);
        $card_number = $request->input('card_number');
        $card_brand = $this->mercadoPagoIntegration->getBinCard($card_number);

        if ($card_brand == null) {
            return response()->json('Não foi possível identificar o cartão.', 422);
        }

        $installments = $this->mercadoPagoIntegration->calcularParcelas($result->total, $card_brand);

        return response()->json([
            'card_brand' => $card_brand,
            'installments' => $installments
        ]);
    }

    public function formatDocument($document)
    {
        return preg_replace('/\D/', '', $document);
    }
}

<?php

namespace App\Services;

use App\Helpers\MessageHelper;
use App\Jobs\ProcessNotificationJob;
use App\Models\Integration;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class InvoiceService
{
	protected $transactionService;

	public function __construct(TransactionService $transactionService)
	{
		$this->transactionService = $transactionService;
	}

	public function getAllInvoices($filters = [])
	{
		$query = Invoice::query();

		if (!empty($filters['name'])) {
			$query->whereHas('user', function ($subQuery) use ($filters) {
				$subQuery->where('name', 'LIKE', '%' . $filters['name'] . '%');
			});
		}

		if (!empty($filters['invoice_id'])) {
			$query->where('id', $filters['invoice_id']);
		}

		if (!empty($filters['status'])) {
			$query->where('status', $filters['status']);
		}

		if (!empty($filters['date_range'])) {
			$datas = explode(' até ', $filters['date_range']);

			if (count($datas) > 1) {
				$filters['start_date'] = Carbon::createFromFormat('d/m/Y', $datas[0])->format('Y-m-d');
				$filters['end_date'] = Carbon::createFromFormat('d/m/Y', $datas[1])->format('Y-m-d');

				if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
					$query->whereBetween('due_at', [$filters['start_date'], $filters['end_date']]);
				}
			} else {
				$dateSearch = Carbon::createFromFormat('d/m/Y', $filters['date_range'])->format('Y-m-d');
				$query->where('due_at', $dateSearch);
			}
		}

		// Faturas filtradas
		$invoices = $query->orderBy('id', 'DESC')->get();

		// Cálculo dos valores
		$totalAmount = $invoices->sum('total');
		$paidAmount = $invoices->where('status', 24)->sum('total'); // Status 24 = Pago
		$unpaidAmount = $invoices->whereIn('status', [23, 25, 28, 29])->sum('total'); // Status pendentes/não pagos

		// Contagem das faturas
		$totalInvoices = $invoices->count();
		$paidInvoices = $invoices->where('status', 24)->count();
		$unpaidInvoices = $invoices->whereIn('status', [23, 25, 28, 29])->count();

		return compact('invoices', 'totalAmount', 'paidAmount', 'unpaidAmount', 'totalInvoices', 'paidInvoices', 'unpaidInvoices');
	}

	public function getInvoiceById($id)
	{
		return Invoice::findOrFail($id);
	}

	public function createInvoice($data)
	{
		DB::beginTransaction(); // Inicia transação para garantir consistência

		try {
			// Criar fatura
			$invoice = Invoice::create([
				'user_id' => $data['user_id'],
				'integration_id' => $data['integration_id'],
				'method_type' => $data['method_type'],
				'total' => collect($data['items'])->sum(fn($item) => $item['price_total']),
				'status' => $data['status'],
				'due_at' => Carbon::parse($data['due_at'])->format('Y-m-d'),
			]);

			// Criar itens da fatura
			foreach ($data['items'] as $item) {
				InvoiceItem::create([
					'invoice_id' => $invoice->id,
					'description' => $item['description'],
					'quantity' => $item['quantity'],
					'price_unit' => $item['price_unit'],
					'price_total' => $item['price_total'],
				]);
			}

			DB::commit(); // Confirma a transação
			return $invoice;
		} catch (Exception $e) {
			DB::rollBack(); // Reverte se houver erro
			throw new Exception("Erro ao criar fatura: " . $e->getMessage());
		}
	}

	public function updateInvoice($id, $data)
	{
		DB::beginTransaction();

		try {
			// Buscar fatura
			$invoice = Invoice::findOrFail($id);

			// Atualizar fatura
			$invoice->update([
				'user_id' => $data['user_id'] ?? $invoice->user_id,
				'integration_id' => $data['integration_id'],
				'method_type' => $data['method_type'],
				'total' => collect($data['items'])->sum(fn($item) => $item['price_total']),
				'status' => $data['status'],
				'due_at' => Carbon::createFromFormat('d/m/Y', $data['due_at'])->format('Y-m-d'),
			]);

			// Remover itens antigos
			InvoiceItem::where('invoice_id', $id)->delete();

			// Adicionar novos itens
			foreach ($data['items'] as $item) {
				InvoiceItem::create([
					'invoice_id' => $invoice->id,
					'description' => $item['description'],
					'quantity' => $item['quantity'],
					'price_unit' => $item['price_unit'],
					'price_total' => $item['price_total'],
				]);
			}

			DB::commit();
			return $invoice;
		} catch (Exception $e) {
			DB::rollBack();
			throw new Exception("Erro ao atualizar fatura: " . $e->getMessage());
		}
	}

	public function deleteInvoice($id)
	{
		$model = Invoice::findOrFail($id);
		return $model->delete();
	}

	public function cancelInvoice($invoiceId)
	{
		$model = Invoice::find($invoiceId);
		if (!$model) {
			return false;
		}

		$model->status = 26;
		$model->save();

		return $model;
	}

	public function confirmPayment($invoiceId, $notifyClient = 1)
	{
		$model = Invoice::find($invoiceId);
		if (!$model) {
			return false;
		}

		$model->status = 24;
		$model->paid_at = Carbon::now();
		$model->save();

		$paymentLocal = Integration::where('slug', 'pagamento-no-local')->first();

		$data_transaction = [
			'invoice_id' => $model->id,
			'integration_id' => $paymentLocal->id,
			'type' => 'income',
			'amount' => $model->total,
			'gateway_fee' => 0,
			'description' => "Pagamento confirmado para a fatura #{$model->id}",
			'date' => now(),
		];

		$this->transactionService->createTransaction($data_transaction);

		if ($notifyClient) {
			$notificationData = [
				'name' => $model->user->name,
				'invoice_id' => $model->id,
            ];

            dispatch(new ProcessNotificationJob('email', $model->user->email, 'Invoice', 'email', 'payment_confirmed', $notificationData));
            if(isset($model->user->phone) && !empty($model->user->phone)) {
                dispatch(new ProcessNotificationJob('whatsapp', $model->user->phone, 'Invoice', 'whatsapp', 'payment_confirmed', $notificationData));
            }			
		}

		return $model;
	}

	public function sendReminder($invoiceId)
	{
		$model = Invoice::find($invoiceId);
		if (!$model) {
			return false;
		}

		$notificationData = [
			'name' => $model->user->name,
			'invoice_id' => $model->id,
			'due_date' => $model->due_at,
		];

		dispatch(new ProcessNotificationJob('email', $model->user->email, 'Invoice', 'email', 'invoice_reminder', $notificationData));
		if(isset($model->user->phone) && !empty($model->user->phone)) {
			dispatch(new ProcessNotificationJob('whatsapp', $model->user->phone, 'Invoice', 'whatsapp', 'invoice_reminder', $notificationData));
		}

		return $model;
	}
}

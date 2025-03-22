<?php

namespace App\Services;

use App\Jobs\ProcessNotificationJob;
use Intervention\Image\ImageManager;
use App\Models\Product;
use App\Models\ProductImageGenerate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ProductService
{
	public function getAllProducts($filters = [])
	{
		$query = Product::query();

		if (!empty($filters['name'])) {
			$query->where('name', 'LIKE', '%' . $filters['name'] . '%');
		}

		if (!empty($filters['status'])) {
			$query->where('status', $filters['status']);
		}

		if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
			$query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
		}

		return $query->limit(20)->get();
	}

	public function getProductById($id)
	{
		return Product::findOrFail($id);
	}

	public function createProduct($data)
	{
		return Product::create($data);
	}

	public function updateProduct($id, $data)
	{
		$model = Product::findOrFail($id);
		$model->update($data);
		return $model;
	}

	public function deleteProduct($id)
	{
		$model = Product::findOrFail($id);
		return $model->delete();
	}

	public function searchProducts($query)
	{
		return Product::where('name', 'like', "%{$query}%")
			->with('affiliateLink') // Relacionamento para obter o link afiliado
			->limit(10) // Limita a 10 resultados
			->get()
			->map(function ($product) {
				return [
					'name' => $product->name,
					'affiliate_link' => $product->affiliateLink->affiliate_link ?? '#',
				];
			});
	}

	private function getTemplateConfig($templateName)
	{
		$configs = [
			'template_modelo_1.png' => [
				'image_x' => 140,
				'image_y' => 0,
				'image_width' => 800,
				'image_height' => 800,
				'text_x' => 525,
				'text_y' => 1520,
				'text_size' => 52,
				'font' => public_path('/galerias/fonts/nyala.ttf'),
				'text_color' => '#4c3018',
			],
			'template_modelo_2.png' => [
				'image_x' => 140,
				'image_y' => 0,
				'image_width' => 800,
				'image_height' => 800,
				'text_x' => 525,
				'text_y' => 1520,
				'text_size' => 52,
				'font' => public_path('/galerias/fonts/nyala.ttf'),
				'text_color' => '#FFFFFF',
			],
		];

		return $configs[$templateName] ?? $configs['template_modelo_1.png']; // Retorna um padrão caso não exista
	}

	public function generateProductImage($product_id)
	{
		$product = Product::findOrFail($product_id);

		if (!is_array($product->images) || empty($product->images)) {
			return response()->json(['error' => 'O produto não tem imagens armazenadas'], 400);
		}
		
		$directory = "public/products/{$product->id}";

		// Verifica se a pasta já existe antes de criá-la
		if (!Storage::exists($directory)) {
			Storage::makeDirectory($directory);
		}
		
		// Converte para o caminho físico real no servidor
		$storagePath = storage_path("app/{$directory}");
		
		// Garante que a pasta foi realmente criada antes de aplicar permissões
		if (file_exists($storagePath)) {
			chmod($storagePath, 0775); // Permissão para leitura/escrita pelo proprietário e grupo
		}
		
		$imagePath = public_path($product->images[0]);

		if (!file_exists($imagePath)) {
			$this->downloadAndStoreImages($product->id);
			$product->refresh();
			$imagePath = public_path(str_replace('/storage', 'storage/app/public', $product->images[0]));

			if (!file_exists($imagePath)) {
				return response()->json(['error' => 'Falha ao baixar a imagem do produto'], 500);
			}
		}

		// Escolhe um template aleatório
		$templatePath = storage_path('app/public/templates/');
		$templates = glob($templatePath . '*.png');
		$randomTemplate = basename($templates[array_rand($templates)]); // Obtém apenas o nome do arquivo

		// Obtém as configurações do template
		$config = $this->getTemplateConfig($randomTemplate);

		$nameOutput = '/social_' . basename($imagePath);
		$outputPath = $directory . $nameOutput;

		$manager = ImageManager::gd();
		$background = $manager->read($templatePath . $randomTemplate);
		$overlay = $manager->read($imagePath);

		// Ajusta tamanho da imagem do produto
		$overlay->resize($config['image_width'], $config['image_height'], function ($constraint) {
			$constraint->aspectRatio();
			$constraint->upsize();
		});

		// Posiciona a imagem conforme o template
		$background->place($overlay, 'left', $config['image_x'], $config['image_y'], 100);

		// Adiciona título do produto
		$text = Str::limit($product->name, 100, '...');
		$text_x = $config['text_x'];
		$text_y = $config['text_y'];
		$text_size = $config['text_size'];

		$background->text($text, $text_x, $text_y, function ($font) use ($config) {
			$font->filename($config['font']);
			$font->size($config['text_size']);
			$font->color($config['text_color']);
			$font->align('center');
			$font->wrap(700);
		});

		// Salva a imagem gerada
		// $background->save($outputPath);

		// Salva a imagem no storage de forma pública
		Storage::disk('public')->put("products/{$product->id}{$nameOutput}", $background->encode());

		$url_image_created = Storage::disk('public')->url("products/{$product->id}{$nameOutput}");

		// Registra no banco para evitar duplicação
		ProductImageGenerate::create(['product_id' => $product->id]);

		$url_image_created = asset('/storage/products/'.$product->id.''.$nameOutput);
		$link_product = $product->getAffiliateLinkByIntegration('shopee');

		$notificationDataImage = [
			'image' => $url_image_created,
		];
		$notificationDataLink = [
			'link' => $link_product,
		];

		dispatch(new ProcessNotificationJob('whatsapp', '16992747526', 'General', 'whatsapp', 'product_send_image', $notificationDataImage));
		dispatch(new ProcessNotificationJob('whatsapp', '16992747526', 'General', 'whatsapp', 'product_send_link', $notificationDataLink));

		return response()->json(['message' => 'Imagem gerada com sucesso!', 'link_affiliate' => $link_product, 'image' => $url_image_created]);
	}

	public function downloadAndStoreImages($product_id)
	{
		$product = Product::findOrFail($product_id);

		if (!is_array($product->images)) {
			return response()->json(['error' => 'Formato de imagem inválido'], 400);
		}

		$localImages = [];
		$directory = "public/products/{$product->id}";

		// Cria o diretório se ele não existir
		if (!Storage::exists($directory)) {
			Storage::makeDirectory($directory);
		}

		// Verifica se a pasta foi realmente criada antes de aplicar permissões
		$storagePath = storage_path("app/{$directory}");
		if (file_exists($storagePath)) {
			chmod($storagePath, 0775);
		}
		
		foreach ($product->images as $index => $imageUrl) {
			try {
				// Faz o download da imagem
				$response = Http::get($imageUrl);
				if ($response->successful()) {
					$extension = pathinfo($imageUrl, PATHINFO_EXTENSION) ?: 'jpg';
					$filename = "product_{$product->id}_{$index}." . $extension;
					$path = "{$directory}/{$filename}";

					// Salva a imagem localmente
					Storage::disk('public')->put($path, $response->body());

					// Adiciona ao array local
					$localImages[] = Storage::url($path);
				}
			} catch (\Exception $e) {
				continue; // Se houver erro em uma imagem, apenas pula para a próxima
			}
		}

		// Atualiza o campo images no banco de dados com os novos caminhos
		if (!empty($localImages)) {
			$product->update(['images' => $localImages]);
		}

		return response()->json(['message' => 'Imagens baixadas com sucesso!', 'images' => $localImages]);
	}
}

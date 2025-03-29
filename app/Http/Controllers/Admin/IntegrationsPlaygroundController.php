<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Integrations\ShopeeIntegration;
use Illuminate\Http\Request;
use App\Services\IntegrationService;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CategoryShopeeImport;
use App\Models\Integration;
use App\Models\IntegrationCategory;
use App\Models\CategoryProduct;
use App\Models\Product;
use App\Models\ProductAffiliateLink;
use App\Models\ProductListJob;
use App\Services\ProductService;
use Illuminate\Support\Str;

class IntegrationsPlaygroundController extends Controller
{
    public $name = 'Playground'; //  singular
    public $folder = 'admin.pages.integrations.playgrounds';

    protected $shopeeIntegration;
    protected $integrationService;
    protected $productService;

    public function __construct(IntegrationService $integrationService, ShopeeIntegration $shopeeIntegration, ProductService $productService)
    {
        $this->shopeeIntegration = $shopeeIntegration;
        $this->integrationService = $integrationService;
        $this->productService = $productService;
    }

    public function index($integration_slug)
    {
        $integration = $this->integrationService->getIntegrationBySlug($integration_slug);

        $data['title'] = $integration->name;
        $data['slug'] = $integration->slug;

        // // Código para importar categorias
        // Excel::import(new CategoryShopeeImport, public_path('galerias/categories_shopee.xlsx'));

        $data['categoriesShopee'] = $integration->integrationCategories;

        return view($this->folder . '.index', $data);
    }

    public function load(Request $request)
    {
        if (!$request->input('type')) {
            return response()->json('Tipo não especificado', 422);
        }

        $filters = $request->all();

        $type = $filters['type'] ?? "shopee_offers";
        $keyword = $filters['keyword'] ?? '';
        $item_id = $filters['item_id'] ?? '';
        $category_id = $filters['category_id'] ?? null;
        $limit = (int) ($filters['limit'] ?? 10);
        $page = (int) ($filters['page'] ?? 1);

        $shopee = $this->shopeeIntegration;

        // Definir variáveis vazias
        $shopeeOffers = [];
        $shopOffers = [];
        $productOffers = [];

        if ($type == 'shopee_offers') {
            $results = $shopee->getShopeeOffers($keyword, $page, $limit);
            $shopeeOffers = $shopee->normalizeShopeeOffers($results);
        } elseif ($type == 'shop_offers') {
            $results = $shopee->getShopOffers($keyword, null, [1, 4], true, 2, "0.05", $page, $limit);
            $shopOffers = $shopee->normalizeShopOffers($results);
        } elseif ($type == 'products_offers') {
            $results = $shopee->getProductsOffers($keyword, $item_id, $category_id, $page, $limit);
            $productOffers = $shopee->normalizeProductOffers($results);
        }

        return view($this->folder . '.index_load', compact('shopeeOffers', 'shopOffers', 'productOffers'));
    }

    public function createProduct(Request $request)
    {
        $result = $request->all();
        $processType = $result['process_type']; // Recebendo a opção do usuário

        if ($processType === 'queue') {
            $randMinutes = rand(2, 15);

            ProductListJob::create([
                'product_data' => json_encode($result),
                'status' => 'pendente',
                'scheduled_at' => now()->addMinues($randMinutes) // Define para rodar na próxima hora
            ]);

            return response()->json('Produto adicionado à fila de cadastro', 200);
        }

        return $this->productService->processProductNow($result);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Integrations\ShopeeIntegration;
use App\Integrations\MercadoLivreIntegration;
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
    protected $mercadoLivreIntegration;
    protected $integrationService;
    protected $productService;

    public function __construct(IntegrationService $integrationService, ShopeeIntegration $shopeeIntegration, MercadoLivreIntegration $mercadoLivreIntegration, ProductService $productService)
    {
        $this->shopeeIntegration = $shopeeIntegration;
        $this->mercadoLivreIntegration = $mercadoLivreIntegration;
        $this->integrationService = $integrationService;
        $this->productService = $productService;
    }

    public function index($integration_slug)
    {
        $integration = $this->integrationService->getIntegrationBySlug($integration_slug);

        $data['title'] = $integration->name;
        $data['slug'] = $integration->slug;

        if ($integration->slug == 'mercado-livre') {
            $data['categoriesMercadoLivre'] = $this->mercadoLivreIntegration->getCategories();
        }

        if ($integration->slug == 'shopee') {
            // // Código para importar categorias
            // Excel::import(new CategoryShopeeImport, public_path('galerias/categories_shopee.xlsx'));
            $data['categoriesShopee'] = $integration->integrationCategories;
        }

        return view($this->folder . '.' . $integration->slug . '.index', $data);
    }

    public function load(Request $request, $integration_slug)
    {
        if (!$request->input('type')) {
            return response()->json('Tipo não especificado', 422);
        }

        $filters = $request->all();

        if ($integration_slug == 'shopee') {
            $this->loadShopee($filters);
        }

        if ($integration_slug == 'mercado-livre') {
            $this->loadMercadoLivre($filters);
        }
    }

    public function loadShopee($filters)
    {
        $type = $filters['type'] ?? "shopee_offers";
        $keyword = $filters['keyword'] ?? '';
        $item_id = $filters['item_id'] ?? '';
        $category_id = $filters['category_id'] ?? null;
        $limit = (int) ($filters['limit'] ?? 10);
        $page = (int) ($filters['page'] ?? 1);
        $sortType = (int) ($filters['sortType'] ?? 2);

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
            $results = $shopee->getProductsOffers($keyword, $item_id, $category_id, $sortType, $page, $limit);
            $productOffers = $shopee->normalizeProductOffers($results);
        }

        return view($this->folder . '.shopee.index_load', compact('shopeeOffers', 'shopOffers', 'productOffers'));
    }

    public function loadMercadoLivre($filters)
    {
        $mercadoLivre = $this->mercadoLivreIntegration;
        $type = $filters['type'] ?? "products";

        // Definir variáveis vazias
        $categories = [];
        $products = [];
        $topProducts = [];

        dd($mercadoLivre->searchProducts());

        if ($type == 'products') {
            $results = $mercadoLivre->searchProducts($filters);
            $products = $results['products'] ?? [];
            $totalProducts = $results['total'] ?? 0;
        } elseif ($type == 'top_products') {
            $topProducts = $mercadoLivre->getSellerTopProducts($filters);
        }

        return view($this->folder . '.mercado-livre.index_load', compact('categories', 'products', 'topProducts', 'totalProducts'));
    }

    public function createProduct(Request $request)
    {
        $result = $request->all();
        $processType = $result['process_type']; // Recebendo a opção do usuário

        if ($processType === 'queue') {
            $randHours = rand(0, 60);
            $randMinutes = rand(10, 45);

            ProductListJob::create([
                'product_data' => json_encode($result),
                'status' => 'pendente',
                'scheduled_at' => \Carbon\Carbon::now('America/Sao_Paulo')->addHours($randHours)->addMinutes($randMinutes),
            ]);

            return response()->json('Produto adicionado à fila de cadastro', 200);
        }

        return $this->productService->processProductNow($result);
    }
}

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

        $integration = Integration::where('slug', $result['slug_integration'])->first();
        $getIntegrationCategory = IntegrationCategory::whereIn('api_category_id', $result['product_categories'])->get();

        $existyProduct = Product::where('name', $result['product_name'])->first();
        if ($existyProduct) {
            $product = $existyProduct;
        } else {
            $product = new Product();
        }
        $product->name = $result['product_name'];
        $product->slug = Str::slug($result['product_name']);
        $product->description = $result['product_description'] ?? null;
        $product->images = [$result['product_images'] ?? null];
        $product->price = $result['product_price_max'] ? $result['product_price_max'] : $result['product_price_min'] ?? 0;
        $product->price_promotion = $result['product_price_min'] ?? 0;
        $product->status = 1;
        $product->save();

        $existyProductAffiliateLink = ProductAffiliateLink::where('api_id', $result['product_id'])->first();
        if ($existyProductAffiliateLink) {
            $procut_affiliate_links = $existyProductAffiliateLink;
        } else {
            $procut_affiliate_links = new ProductAffiliateLink();
        }
        $procut_affiliate_links->product_id = $product->id;
        $procut_affiliate_links->integration_id = $integration->id;
        $procut_affiliate_links->affiliate_link = $result['product_link'];
        $procut_affiliate_links->api_id = $result['product_id'] ?? null;
        $procut_affiliate_links->save();

        // Associar o produto com as categorias usando sync()
        $product->categories()->sync($getIntegrationCategory->pluck('category_id')->toArray());

        $this->productService->downloadAndStoreImages($product->id);

        $this->productService->generateProductStory($product->id);

        $this->productService->publishProductImage($product->id);

        return response()->json('Produto Cadastrado/Atualizado com Sucesso', 200);
    }
}

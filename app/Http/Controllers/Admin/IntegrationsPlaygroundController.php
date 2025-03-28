<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Integrations\ShopeeIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\Status;
use App\Models\Integration;
use Carbon\Carbon;
use App\Services\IntegrationService;
use Illuminate\Support\Facades\Cache;

class IntegrationsPlaygroundController extends Controller
{
    public $name = 'Playground'; //  singular
    public $folder = 'admin.pages.integrations.playgrounds';

    protected $shopeeIntegration;
    protected $integrationService;

    public function __construct(IntegrationService $integrationService, ShopeeIntegration $shopeeIntegration)
    {
        $this->shopeeIntegration = $shopeeIntegration;
        $this->integrationService = $integrationService;
    }

    public function index($integration_slug)
    {
        $integration = $this->integrationService->getIntegrationBySlug($integration_slug);

        $data['title'] = $integration->name;
        $data['slug'] = $integration->slug;

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

}

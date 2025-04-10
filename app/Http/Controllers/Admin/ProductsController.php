<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Integration;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\Status;
use App\Services\CategoryService;
use App\Services\ProductService;
use Carbon\Carbon;

class ProductsController extends Controller
{
    public $name = 'Produto'; //  singular
    public $folder = 'admin.pages.products';

    protected $productService;
    protected $categoryService;

    public function __construct(ProductService $productService, CategoryService $categoryService)
    {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        $integrations = Integration::where('type', 'marketplaces')->get();

        return view($this->folder . '.index', compact('integrations'));
    }

    public function load(Request $request)
    {
        $query = [];
        $filters = $request->only(['name', 'status', 'date_range']);

        if (!empty($filters['name'])) {
            $query['name'] = $filters['name'];
        }

        if (!empty($filters['status'])) {
            $query['status'] = $filters['status'];
        }

        if (!empty($filters['date_range'])) {
            [$startDate, $endDate] = explode(' até ', $filters['date_range']);
            $query['start_date'] = Carbon::createFromFormat('d/m/Y', $startDate)->format('Y-m-d');
            $query['end_date'] = Carbon::createFromFormat('d/m/Y', $endDate)->format('Y-m-d');
        }

        $results = $this->productService->getAllProducts($filters);


        return view($this->folder . '.index_load', compact('results'));
    }

    public function create()
    {
        $statuses = Status::default();
        $integrations = Integration::where('type', 'marketplaces')->get();

        return view($this->folder . '.form', compact('statuses', 'integrations'));
    }

    public function store(Request $request)
    {
        $result = $request->all();

        $rules = array(
            'name' => 'required|unique:products,name',
            'slug' => 'required|unique:products,slug',
            'images' => 'required',
            'price' => 'required',
            'status' => 'required',
        );
        $messages = array(
            'name.required' => 'name é obrigatório',
            'name.unique' => 'nome já existe',
            'slug.required' => 'url amigável é obrigatório',
            'slug.unique' => 'nome amigável já existe',
            'images.required' => 'images é obrigatório',
            'price.required' => 'price é obrigatório',
            'status.required' => 'status é obrigatório',
        );

        $validator = Validator::make($result, $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 422);
        }

        // Convertendo imagens (separadas por vírgula) para JSON
        $result['images'] = array_map('trim', explode(',', $result['images']));

        // Convertendo preço (removendo caracteres indesejados como vírgula no formato brasileiro)
        $result['price'] = floatval(str_replace(',', '.', $result['price']));
        $result['price_promotion'] = isset($result['price_promotion']) ? floatval(str_replace(',', '.', $result['price_promotion'])) : null;

        $product = $this->productService->createProduct($result);

        // Relacionando categorias (Many-to-Many)
        $product->categories()->sync($result['categories']);

        if (!empty($result['marketplace']) && !empty($result['affiliate_links'])) {
            foreach ($result['marketplace'] as $index => $marketplaceId) {
                if (!empty($marketplaceId) && !empty($result['affiliate_links'][$index])) {
                    $product->affiliateLinks()->create([
                        'integration_id' => $marketplaceId,
                        'affiliate_link' => $result['affiliate_links'][$index],
                    ]);
                }
            }
        }

        return response()->json($this->name . ' adicionado com sucesso', 200);
    }

    public function edit($id)
    {
        $result = $this->productService->getProductById($id);
        $statuses = Status::default();
        $integrations = Integration::where('type', 'marketplaces')->get();

        return view($this->folder . '.form', compact('result', 'statuses', 'integrations'));
    }

    public function update(Request $request, $id)
    {
        $result = $request->all();

        $rules = array(
            'name' => "required|unique:products,name,$id,id",
            'slug' => "required|unique:products,slug,$id,id",
            'images' => 'required',
            'price' => 'required',
            'status' => 'required',
        );
        $messages = array(
            'name.required' => 'name é obrigatório',
            'name.unique' => 'name já está sendo utilizado',
            'slug.required' => 'slug é obrigatório',
            'slug.unique' => 'slug já está sendo utilizado',
            'images.required' => 'images é obrigatório',
            'price.required' => 'price é obrigatório',
            'status.required' => 'status é obrigatório',
        );

        $validator = Validator::make($result, $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 422);
        }

        // Convertendo imagens (separadas por vírgula) para JSON
        $result['images'] = array_map('trim', explode(',', $result['images']));

        // Convertendo preço (removendo caracteres indesejados como vírgula no formato brasileiro)
        $result['price'] = floatval(str_replace(',', '.', $result['price']));
        $result['price_promotion'] = isset($result['price_promotion']) ? floatval(str_replace(',', '.', $result['price_promotion'])) : null;

        $product = $this->productService->updateProduct($id, $result);

        // Relacionando categorias (Many-to-Many)
        $product->categories()->sync($result['categories']);

        $product->affiliateLinks()->delete();

        if (!empty($result['marketplace']) && !empty($result['affiliate_links'])) {
            foreach ($result['marketplace'] as $index => $marketplaceId) {
                // Verifique se tanto o marketplaceId quanto o link são válidos
                if (!empty($marketplaceId) && !empty($result['affiliate_links'][$index])) {
                    $product->affiliateLinks()->create([
                        'integration_id' => $marketplaceId,
                        'affiliate_link' => $result['affiliate_links'][$index],
                    ]);
                }
            }
        }

        return response()->json($this->name . ' atualizado com sucesso', 200);
    }

    public function delete($id)
    {
        $this->productService->deleteProduct($id);

        return response()->json($this->name . ' excluído com sucesso', 200);
    }

    public function generateImageStory($id)
    {
        $result = $this->productService->generateProductStory($id);

        return $result;
    }

    public function generateImageFeed($id)
    {
        $result = $this->productService->publishProductImage($id);

        return response()->json($result['title'], $result['status']);
    }

    public function generateSuggestions()
    {
        // Busca uma categoria pai aleatória e carrega as subcategorias e os produtos
        $category = Category::with(['products.affiliateLinks', 'children.products'])->whereNull('parent_id')->inRandomOrder()->first();

        if (!$category) {
            return response()->json(['error' => 'Nenhuma categoria encontrada.'], 404);
        }

        $products = [];

        foreach ($category->children as $child) {
            $product = $child->randomProduct();

            if ($product) {
                $product_name = $product->name;
                $product_link = $product->getAffiliateLinkByIntegration('shopee') ?? '#';
                $products[] = [
                    'name' => $product_name,
                    'link' => $product_link,
                ];
            }
        }

        return response()->json([
            'category' => $category->name,
            'products' => $products
        ]);
    }

    protected function putFacebookCatalog($product_id)
    {
        $product = Product::find($product_id);
        dd($product);

        $catalogId = '1359397078637160';

        $accessToken = 'EABZCOLCOKmv4BOZB1PTbpW3BVPWKml4fChmj3rNZAE2AJdwnZBBzICT6f8FqEpVnlxcur0IZBzcKXgYrVAl7ARuPiw6XY7hE6b15OXJPZC9MJoLoE2DnxuOdiXq6PlYvVU7wKW0PLle4VCNiO3CE7VTc5lo9mRwvYVQHzVJjTWU0XCz1UGEhq4oQZBo4rMNeSQ4';

        $data = [
            'retailer_id' => $product->id,
            'name' => $product->title,
            'description' => $product->title,
            'price' => round($product->price * 100), // Convertendo para centavos
            'currency' => 'BRL',
            'availability' => 'in stock', // Exemplo: in stock, out of stock
            'condition' => 'new', // Exemplo: new, refurbished
            'image_url' => url($product->image),
            'url' => $product->link,
        ];

        //Log::info(json_encode($data));


        // Verificar se o produto já existe no catálogo


        $responseCheck = Http::withToken($accessToken)
            ->get("https://graph.facebook.com/v17.0/" . $catalogId . "/products", [
                'filter' => '{"retailer_id":{"eq":"' . $product_id . '"}}',
            ]);

        if ($responseCheck->failed()) {
            Log::error("Erro ao consultar o produto {$product->id}: " . $responseCheck->body());
        }

        $existingProduct = $responseCheck->json();

        // Se encontrar o produto, tenta atualizar, caso contrário, cria um novo
        if (isset($existingProduct['data']) && count($existingProduct['data']) > 0) {
            $existingProductId = $existingProduct['data'][0]['id'];
            $responseUpdate = Http::withToken($accessToken)
                ->post("https://graph.facebook.com/v17.0/{$existingProductId}", $data);

            if ($responseUpdate->failed()) {
                Log::error("Erro ao atualizar produto {$product->id}: " . $responseUpdate->body());
            } else {
                Log::info("Produto {$product->id} atualizado com sucesso.");
            }
        } else {
            $responseCreate = Http::withToken($accessToken)
                ->post("https://graph.facebook.com/v17.0/{$catalogId}/products", $data);

            if ($responseCreate->failed()) {
                Log::error("Erro ao criar produto {$product->id}: " . $responseCreate->body());
            } else {
                Log::info("Produto {$product->id} criado com sucesso.");
            }
        }
    }
}

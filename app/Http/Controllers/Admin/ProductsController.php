<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Integration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Status;
use App\Models\Product;
use App\Services\CategoryService;
use App\Services\ProductService;
use Carbon\Carbon;
use App\Jobs\GenerateProductImageJob;
use Illuminate\Support\Facades\Log;

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

    public function generateImage($id)
    {
        $result = $this->productService->generateProductImage($id);

        return $result;
    }

    public function checkImages()
    {
        // Busca todos os produtos
        $products = Product::all();

        foreach ($products as $product) {
            // Verifica se a coluna images é válida
            if (!is_array($product->images) || empty($product->images)) {
                continue; // Pula para o próximo produto
            }

            // Verifica se alguma das imagens ainda está com URL externa (não salva localmente)
            $hasExternalImage = collect($product->images)->contains(function ($image) {
                return !Str::startsWith($image, '/storage'); // Verifica se NÃO começa com "/storage"
            });

            // Se tiver alguma imagem externa, chama a função de download
            if ($hasExternalImage) {
                $this->productService->downloadAndStoreImages($product->id);
            }
        }

        return response()->json(['message' => 'Verificação concluída!']);
    }

    public function sugestions()
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

    public function randomCreate()
    {
        // Busca um produto que ainda não teve a imagem gerada
        $product = Product::whereDoesntHave('generatedImages')->inRandomOrder()->first();

        if ($product) {
            dispatch(new GenerateProductImageJob($product));

            Log::info("Job de geração de imagem disparada para o produto ID: " . $product->id . " " . $product->name);
        }

        return redirect()->route('admin.products.index');
    }

    protected function generateImageFeed($product_id)
    {

        $product = Product::find($product_id);

        $social_image = asset($product->images[0]);

        // \Log::info('Social Image: ' . $social_image);

        //$baseUrl = 'http://mayofertas.local:8090/api/facebook';
        $baseUrl = "https://multisocial.chat/api/facebook";
        $queryParams = [
            'token'             => 'm7ThIZbEzdquOsY57IAvoSS6k1ZTdrLZ1u760QZuUF13gHfOLHGA5YWH0dtqccCT',
            'facebook_meta_id'  => 60,
            'name'              => $product->name,
            'content'           => $product->name,
            'media'             => $social_image,
            'local'             => ['instagram_post', 'facebook_post'],
            'mark_product'      => 0,
            'catalog_id'        => '',
            'retailer_id'       => $product_id,

        ];

        // Constrói a URL com query strings automaticamente
        $urlWithParams = $baseUrl . '?' . http_build_query($queryParams);

        // \Log::info('UrlParams: ' . $urlWithParams);

        // Fazer a requisição
        $response = Http::post($urlWithParams);

        //dd($response->body());

        \Log::info('Response:' . json_encode($response->body()));

        if ($response->successful()) {
            return response()->json('Postagem publicada com sucesso!', 200);
        }

        if (!$response->successful()) {
            \Log::info('badRequest:' . $response->body());

            return response()->json($response->body(), 422);
        }

        // Verificar se a requisição foi bem-sucedida
        if ($response->failed()) {
            return response()->json('Erro ao postar nas redes sociais', 422);
        }
    }
}

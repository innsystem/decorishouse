<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['name', 'status', 'start_date', 'end_date']);
        return response()->json($this->productService->getAllProducts($filters));
    }

    public function show($id)
    {
        return response()->json($this->productService->getProductById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:products,name',
            'slug' => 'required|string|unique:products,slug',
            'images' => 'required|string',
            'price' => 'required|string',
            'status' => 'required|string',
        ]);

        // Adicionando valores padrão para evitar erro
        $data['price_promotion'] = $request->input('price_promotion', null);
        $data['categories'] = $request->input('categories', []); // Caso não tenha categorias
        $data['marketplace'] = $request->input('marketplace', []);
        $data['affiliate_links'] = $request->input('affiliate_links', []);

        // Convertendo imagens (separadas por vírgula) para JSON
        $data['images'] = array_map('trim', explode(',', $request->input('images')));

        // Formatando preço corretamente
        $data['price'] = floatval(str_replace(',', '.', $data['price']));
        if ($data['price_promotion']) {
            $data['price_promotion'] = floatval(str_replace(',', '.', $data['price_promotion']));
        }

        // Criando o produto
        $product = $this->productService->createProduct($data);

        // Relacionando categorias (Many-to-Many)
        if (!empty($data['categories'])) {
            $product->categories()->sync($data['categories']);
        }

        // Salvando links afiliados se houver
        if (!empty($data['marketplace']) && !empty($data['affiliate_links'])) {
            foreach ($data['marketplace'] as $index => $marketplaceId) {
                if (!empty($marketplaceId) && !empty($data['affiliate_links'][$index])) {
                    $product->affiliateLinks()->create([
                        'integration_id' => $marketplaceId,
                        'affiliate_link' => $data['affiliate_links'][$index],
                    ]);
                }
            }
        }

        $this->productService->downloadAndStoreImages($product->id);

        $this->productService->generateProductStory($product->id);

        return response()->json(['message' => 'Produto adicionado com sucesso'], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate(array(
            'name' => 'required|string',
            'description' => 'string',
            'images' => 'required|string',
            'price' => 'required|string',
            'price_promotion' => 'string',
            'status' => 'required|string',
        ));
        return response()->json($this->productService->updateProduct($id, $data));
    }

    public function destroy($id)
    {
        $this->productService->deleteProduct($id);
        return response()->json(['message' => 'Product deleted']);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        
        if (strlen($query) < 3) {
            return response()->json(['message' => 'A pesquisa deve ter pelo menos 3 caracteres'], 400);
        }
        
        $products = $this->productService->searchProducts($query);
        
        return response()->json($products);
    }

    /**
     * Retorna os produtos mais recentes
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recent(Request $request)
    {
        $limit = $request->input('limit', 5);
        $products = $this->productService->getRecentProducts($limit);
        
        return response()->json($products);
    }

    /**
     * Retorna os produtos em promoção
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function promotions(Request $request)
    {
        $limit = $request->input('limit', 5);
        $products = $this->productService->getPromotionalProducts($limit);
        
        return response()->json($products);
    }
}

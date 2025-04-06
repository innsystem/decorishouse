<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Service;
use App\Models\Portfolio;
use App\Models\Product;
use App\Models\ProductAffiliateLink;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use App\Services\ProductService;

class SiteController extends Controller
{    
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index()
    {
        // Buscar categorias principais (sem parent_id)
        $mainCategories = Category::where('parent_id', null)
            ->where('status', 1)
            ->get();
            
        // Para cada categoria principal, buscar produtos (limitado a 24 por categoria)
        $categoriesWithProducts = [];
        
        foreach ($mainCategories as $category) {
            // Obter IDs da categoria atual e todas as suas subcategorias
            $categoryIds = [$category->id];
            
            // Adicionar subcategorias
            $subcategories = $category->children;
            foreach ($subcategories as $subcategory) {
                $categoryIds[] = $subcategory->id;
            }
            
            // Buscar produtos destas categorias
            $products = Product::whereHas('categories', function ($query) use ($categoryIds) {
                    $query->whereIn('categories.id', $categoryIds);
                })
                ->with(['affiliateLinks.integration'])
                ->orderBy('created_at', 'desc')
                ->limit(24)
                ->get();
                
            // Adicionar apenas se houver produtos
            if ($products->count() > 0) {
                $categoriesWithProducts[] = [
                    'category' => $category,
                    'products' => $products
                ];
            }
        }
        
        return view('site.pages.home', compact('categoriesWithProducts'));
    }

    public function pageShow($slug)
    {
        $page = Page::where('slug', $slug)->first();

        return view('examples.pages_show', compact('page'));
    }

    public function serviceShow($slug)
    {
        $service = Service::where('slug', $slug)->first();

        return view('examples.services_show', compact('service'));
    }

    public function portfolioShow($slug)
    {
        $portfolio = Portfolio::where('slug', $slug)->first();

        return view('examples.portfolios_show', compact('portfolio'));
    }

    public function trackClick(Request $request)
    {
        $productLink = ProductAffiliateLink::find($request->product_link_id);

        if ($productLink) {
            $productLink->increment('clicks'); // Aumenta +1 no campo 'clicks'
            return response()->json(['success' => true, 'message' => 'Clique registrado com sucesso!']);
        }

        return response()->json(['success' => false, 'message' => 'Produto não encontrado'], 404);
    }

    public function categoryShow($slug)
    {
        $category = Category::where('slug', $slug)->first();

        if (!$category) {
            return redirect()->route('site.index')->with('error', 'Categoria não encontrada.');
        }
        
        // Criar um array com os IDs da categoria atual e todas as suas subcategorias
        $categoryIds = [$category->id];
        
        // Se for uma categoria principal (sem parent_id), adicionar os IDs de todas as subcategorias
        if (!$category->parent_id) {
            $subcategories = $category->children;
            foreach ($subcategories as $subcategory) {
                $categoryIds[] = $subcategory->id;
            }
        }
        
        // Buscar produtos de todas as categorias relevantes com paginação
        $productsQuery = Product::whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->with(['affiliateLinks.integration'])
            ->orderBy('created_at', 'desc');
            
        $products = $productsQuery->paginate(24); // 24 produtos por página
        
        return view('site.pages.category_show', compact('category', 'products'));
    }

    public function searchProducts(Request $request)
    {
        $query = $request->input('query');

        if (strlen($query) < 3) {
            return response()->json([]);
        }

        $products = $this->productService->searchProducts($query);

        return response()->json($products);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        
        if (empty($query) || strlen($query) < 3) {
            return redirect()->route('site.index')
                ->with('error', 'Por favor, digite pelo menos 3 caracteres para realizar a busca.');
        }
        
        // Buscar produtos que contenham a consulta no nome ou descrição
        $productsQuery = Product::where('name', 'like', '%' . $query . '%')
            ->orWhere('description', 'like', '%' . $query . '%')
            ->with(['affiliateLinks.integration'])
            ->orderBy('created_at', 'desc');
            
        $products = $productsQuery->paginate(24); // 24 produtos por página
        
        return view('site.pages.search_results', compact('products', 'query'));
    }
}

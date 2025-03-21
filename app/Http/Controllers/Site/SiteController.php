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
        $promotionsRecents = ProductAffiliateLink::orderBy('created_at', 'DESC')->limit(12)->get();
        $promotionsRandoms = ProductAffiliateLink::inRandomOrder()->limit(12)->get();

        return view('site.pages.home', compact('promotionsRecents', 'promotionsRandoms'));
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
        
        $products = $category->productAffiliateLinks;

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
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessFacebookCatalogJob;
use App\Services\ProductService;
use Illuminate\Http\Request;

class FacebookCatalogController extends Controller
{
    /**
     * Inicia a sincronização do catálogo Facebook em background
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncCatalog(Request $request)
    {
        $batchSize = (int) $request->input('batch_size', 10);
        
        if ($batchSize <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'O tamanho do lote deve ser maior que zero'
            ], 400);
        }
        
        // Inicia a job em background
        ProcessFacebookCatalogJob::dispatch($batchSize, 0);
        
        return response()->json([
            'success' => true,
            'message' => 'Sincronização do catálogo iniciada em segundo plano',
            'details' => [
                'batch_size' => $batchSize,
                'status' => 'processing'
            ]
        ]);
    }
    
    /**
     * Sincroniza um único produto com o catálogo do Facebook
     * 
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncProduct(Request $request, $productId)
    {
        $productService = new ProductService();
        
        try {
            $result = $productService->facebookCatalog($productId);
            return response()->json([
                'success' => true,
                'message' => 'Produto sincronizado com sucesso',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao sincronizar produto',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 
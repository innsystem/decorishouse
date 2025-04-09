<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    /**
     * Formata uma lista de produtos para mensagem no formato WhatsApp
     * 
     * Esta API recebe uma lista de produtos e formata como uma mensagem amigável
     * para compartilhamento no WhatsApp e outras plataformas de mensagens.
     * 
     * Parâmetros aceitos:
     * - produtos: Array de produtos com name, price, price_promotion, affiliate_link
     * - search_results_json: String JSON com resultados de pesquisa (alternativa para produtos)
     * - titulo: Texto de cabeçalho da mensagem (padrão: '🛒 Produtos encontrados:')
     * - incluir_preco: Boolean para mostrar ou não os preços (padrão: true)
     * - incluir_link: Boolean para mostrar ou não os links (padrão: true)
     * - limite: Número máximo de produtos a incluir (padrão: 10)
     * - rodape: Texto opcional para o rodapé da mensagem
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function formatWhatsAppMessage(Request $request)
    {
        $search_results_json = $request->input('search_results_json', '');
        $produtos = $request->input('produtos', []);
        $titulo = $request->input('titulo', '🛒 Produtos encontrados:');
        $incluirPreco = $request->input('incluir_preco', true);
        $incluirLink = $request->input('incluir_link', true);
        $limiteProdutos = $request->input('limite', 10);
        $rodape = $request->input('rodape', '');
        
        // Se recebermos JSON de resultados de pesquisa, tentamos decodificar
        if (!empty($search_results_json) && empty($produtos)) {
            try {
                // Registra no log o que foi recebido
                Log::info('Recebido search_results_json: ' . $search_results_json);
                
                // Tenta corrigir e decodificar o JSON
                // Primeiro verifica se precisamos adicionar colchetes para fazer um array
                $jsonCorrigido = $search_results_json;
                
                // Verificar se o JSON parece uma sequência de objetos sem estar em um array
                // (quando começa com { e contém várias ocorrências de "id":)
                if (substr(trim($jsonCorrigido), 0, 1) === '{' && 
                    substr_count($jsonCorrigido, '"id":') > 1) {
                    // Adiciona colchetes se parecer uma sequência de objetos sem estar em um array
                    $jsonCorrigido = '[' . $jsonCorrigido . ']';
                    Log::info('JSON corrigido com colchetes adicionados: ' . $jsonCorrigido);
                }
                
                // Verifica por problemas de truncamento
                $lastChar = substr(trim($jsonCorrigido), -1);
                if ($lastChar !== ']' && $lastChar !== '}') {
                    // Tenta encontrar o último objeto JSON completo
                    $lastValidJson = $this->encontrarUltimoObjetoValido($jsonCorrigido);
                    if (!empty($lastValidJson)) {
                        $jsonCorrigido = $lastValidJson;
                        Log::info('JSON corrigido após truncamento: ' . $jsonCorrigido);
                    }
                }
                
                // Trata formatações incorretas conhecidas
                $jsonCorrigido = str_replace('}{', '},{', $jsonCorrigido);
                
                // Tenta decodificar o JSON corrigido
                $decoded = json_decode($jsonCorrigido, true);
                
                if ($decoded === null) {
                    Log::error('Falha ao decodificar JSON mesmo após correções: ' . json_last_error_msg());
                    
                    // Tentativa com regex para extrair produtos individuais
                    $produtos = $this->extrairProdutosPorRegex($search_results_json);
                    if (!empty($produtos)) {
                        Log::info('Produtos extraídos por regex: ' . count($produtos));
                    }
                } 
                // Se for um objeto único, transforma em array com um item
                elseif (is_array($decoded) && isset($decoded['name'])) {
                    $produtos = [$decoded];
                    Log::info('Produto único decodificado com sucesso');
                } 
                // Se já for um array de produtos
                elseif (is_array($decoded) && !empty($decoded)) {
                    $produtos = $decoded;
                    Log::info('Array de produtos decodificado com sucesso: ' . count($produtos) . ' produtos');
                }
                // Se não for nenhum dos formatos esperados
                else {
                    Log::info('JSON em formato não esperado: ' . print_r($decoded, true));
                }
            } catch (\Exception $e) {
                Log::error('Erro ao processar JSON: ' . $e->getMessage());
            }
        }
        
        // Se não tiver produtos, retorna mensagem de não encontrado
        if (empty($produtos)) {
            return response()->json([
                'mensagem' => 'Nenhum produto encontrado para sua pesquisa. Tente novamente com outras palavras-chave.',
                'produtos_formatados' => 0
            ]);
        }
        
        // Limitar a quantidade de produtos
        $produtos = array_slice($produtos, 0, $limiteProdutos);
        
        $mensagem = "{$titulo}\n\n";
        
        foreach ($produtos as $index => $produto) {
            $numeroEmoji = $this->numeroParaEmoji($index + 1);
            
            // Nome do produto
            $mensagem .= "{$numeroEmoji} *{$produto['name']}*\n";
            
            // Preço (opcional)
            if ($incluirPreco) {
                $preco = number_format(floatval($produto['price'] ?? 0), 2, ',', '.');
                $mensagem .= "💰 R$ {$preco}\n";
                
                // Se tiver preço promocional
                if (!empty($produto['price_promotion']) && $produto['price_promotion'] > 0) {
                    $precoPromo = number_format(floatval($produto['price_promotion']), 2, ',', '.');
                    $mensagem .= "🔥 Promoção: R$ {$precoPromo}\n";
                }
            }
            
            // Link afiliado (opcional)
            if ($incluirLink && !empty($produto['affiliate_link']) && $produto['affiliate_link'] != '#') {
                $mensagem .= "🔗 [Ver produto]({$produto['affiliate_link']})\n\n";
            } else {
                $mensagem .= "\n";
            }
        }
        
        // Adicionar rodapé se existir
        if (!empty($rodape)) {
            $mensagem .= "{$rodape}\n";
        }
        
        return response()->json([
            'mensagem' => $mensagem,
            'produtos_formatados' => count($produtos)
        ]);
    }
    
    /**
     * Tenta encontrar o último objeto JSON válido em uma string truncada
     *
     * @param string $jsonString
     * @return string
     */
    private function encontrarUltimoObjetoValido($jsonString)
    {
        // Verifica se a string parece estar no formato de um array de objetos
        if (substr(trim($jsonString), 0, 1) === '[') {
            // Encontra o último objeto completo em um array
            $matches = [];
            preg_match_all('/(\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\})/', $jsonString, $matches);
            
            if (!empty($matches[0])) {
                // Reconstroí o array com objetos válidos
                return '[' . implode(',', $matches[0]) . ']';
            }
        } elseif (substr(trim($jsonString), 0, 1) === '{') {
            // Para um único objeto, tenta extrair o objeto completo
            $matches = [];
            if (preg_match('/(\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\})/', $jsonString, $matches)) {
                return $matches[0];
            }
        }
        
        return '';
    }
    
    /**
     * Extrai produtos de um JSON malformado usando regex
     *
     * @param string $jsonString
     * @return array
     */
    private function extrairProdutosPorRegex($jsonString)
    {
        $produtos = [];
        $pattern = '/"id"\s*:\s*(\d+)\s*,\s*"name"\s*:\s*"([^"]+)"\s*,\s*"price"\s*:\s*"([^"]+)"\s*(?:,\s*"price_promotion"\s*:\s*"([^"]+)")?\s*(?:,\s*[^{}]+)?\s*,\s*"affiliate_link"\s*:\s*"([^"]+)"/i';
        
        preg_match_all($pattern, $jsonString, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $produtos[] = [
                'id' => $match[1] ?? '',
                'name' => $match[2] ?? '',
                'price' => $match[3] ?? 0,
                'price_promotion' => $match[4] ?? null,
                'affiliate_link' => $match[5] ?? '#'
            ];
        }
        
        return $produtos;
    }

    /**
     * Converte um número para emoji de número
     *
     * @param int $numero
     * @return string
     */
    private function numeroParaEmoji($numero)
    {
        $emojis = [
            '1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣',
            '6️⃣', '7️⃣', '8️⃣', '9️⃣', '🔟'
        ];
        
        if ($numero >= 1 && $numero <= 10) {
            return $emojis[$numero - 1];
        }
        
        return $numero . '⃣';
    }
}

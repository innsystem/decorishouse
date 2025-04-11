<?php

namespace App\Integrations;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\Integration;
use App\Models\ProductAffiliateLink;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;

class MercadoLivreIntegration
{
    protected $client;
    protected $accessToken;
    protected $baseUri;
    protected $siteId;

    public function __construct()
    {
        // Garante que o timezone do servidor está correto
        date_default_timezone_set('UTC');

        $settings = $this->getSettings();

        if (!$settings) {
            throw new \Exception('Credenciais do Mercado Livre não encontradas.');
        }

        $this->accessToken = $settings['access_token'] ?? null;
        $this->baseUri = 'https://api.mercadolibre.com';
        $this->siteId = $settings['site_id'] ?? 'MLB';

        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->accessToken
            ]
        ]);
    }

    public function getIntegration()
    {
        return Integration::where('slug', 'mercado-livre')->first();
    }

    public function getSettings()
    {
        $integration = $this->getIntegration();
        return $integration->settings ?? null;
    }

    /**
     * Método para buscar categorias do Mercado Livre
     * 
     * @param string $siteId ID do site (país) do Mercado Livre
     * @return array
     */
    public function getCategories($siteId = null)
    {
        $siteId = $siteId ?? $this->siteId;
        $url = "{$this->baseUri}/sites/{$siteId}/categories";

        try {
            $response = Http::get($url);

            if ($response->failed()) {
                return $this->handleError('Falha ao buscar categorias do Mercado Livre', $response->status());
            }

            return $response->json();
        } catch (\Exception $e) {
            return $this->handleGeneralException($e);
        }
    }

    public function getUsers()
    {
        // https://api.mercadolibre.com/users/me
        $url = "/users/me";

        $response = $this->client->request('GET', $url);
        return json_decode($response->getBody(), true);
    }

    /**
     * Método para buscar produtos por categoria e outros filtros
     * 
     * @param array $filters Filtros de busca
     * @return array
     */
    public function searchProducts($filters = [])
    {
        $categoryId = $filters['category'] ?? null;
        $limit = $filters['limit'] ?? 10;
        $sort = $filters['sort'] ?? 'relevance';
        $query = $filters['query'] ?? '';
        $freeShipping = $filters['freeShipping'] ?? false;
        $page = $filters['page'] ?? 1;
        $siteId = $filters['site_id'] ?? $this->siteId;

        // Monta a URL da API com os parâmetros
        $url = "/sites/{$siteId}/search";
        
        $queryParams = [
            'limit' => $limit,
            'offset' => (($page - 1) * $limit),
            'sort' => $sort
        ];

        if (!empty($categoryId)) {
            $queryParams['category'] = $categoryId;
        }

        if (!empty($query)) {
            $queryParams['q'] = $query;
        }

        if (filter_var($freeShipping, FILTER_VALIDATE_BOOLEAN)) {
            $queryParams['shipping_cost'] = 'free';
        }

        // Usar o cliente GuzzleHttp configurado com o token de acesso
        $response = $this->client->request('GET', $url, [
            'query' => $queryParams
        ]);

        try {
            
            $data = json_decode($response->getBody(), true);
            return $this->normalizeProductResults($data, $freeShipping);
        } catch (ClientException $e) {
            return $this->handleClientException($e);
        } catch (RequestException $e) {
            return $this->handleRequestException($e);
        } catch (\Exception $e) {
            return $this->handleGeneralException($e);
        }
    }

    /**
     * Normaliza os resultados da busca de produtos
     * 
     * @param array $data Dados retornados pela API
     * @param bool $checkFreeShipping Verificar frete grátis manualmente
     * @return array
     */
    public function normalizeProductResults($data, $checkFreeShipping = false)
    {
        if (!isset($data['results']) || !is_array($data['results'])) {
            return [
                'products' => [],
                'total' => 0,
                'error' => 'Nenhum produto encontrado'
            ];
        }

        $products = $data['results'];
        $total = $data['paging']['total'] ?? 0;

        // Filtra manualmente os produtos para garantir o frete grátis (caso necessário)
        if ($checkFreeShipping) {
            $products = array_filter($products, function ($product) {
                return isset($product['shipping']['free_shipping']) && $product['shipping']['free_shipping'] === true;
            });
        }

        // Mapeia os dados necessários
        $mappedProducts = array_map(function ($product) {
            return [
                'id' => $product['id'],
                'title' => $product['title'],
                'permalink' => $product['permalink'],
                'category_id' => $product['category_id'],
                'category_name' => $product['attributes'][0]['value_name'] ?? 'N/A',
                'price' => $product['price'],
                'thumbnail' => $product['thumbnail'],
                'free_shipping' => $product['shipping']['free_shipping'] ?? false,
            ];
        }, $products);

        return [
            'products' => $mappedProducts,
            'total' => $total,
            'paging' => $data['paging'] ?? []
        ];
    }

    /**
     * Método para buscar produtos mais vendidos de um vendedor
     * 
     * @param array $params Parâmetros para a busca
     * @return array
     */
    public function getSellerTopProducts($params = [])
    {
        $categoryId = $params['category'] ?? 'MLB1648';
        $limit = $params['limit'] ?? 10;
        $sort = $params['sort'] ?? 'sold_quantity_desc';
        $sellerId = $params['seller_id'] ?? null;
        $siteId = $params['site_id'] ?? $this->siteId;

        $url = "{$this->baseUri}/sites/{$siteId}/search";
        
        $queryParams = [
            'category' => $categoryId,
            'limit' => $limit,
            'sort' => $sort
        ];

        if ($sellerId) {
            $queryParams['seller_id'] = $sellerId;
        }

        try {
            $headers = [];
            if ($this->accessToken) {
                $headers['Authorization'] = 'Bearer ' . $this->accessToken;
            }

            $response = Http::withHeaders($headers)->get($url, $queryParams);

            if ($response->failed()) {
                return $this->handleError('Erro ao buscar produtos mais vendidos', $response->status());
            }

            return $response->json()['results'];
        } catch (\Exception $e) {
            return $this->handleGeneralException($e);
        }
    }

    /**
     * Processa um erro de resposta HTTP
     * 
     * @param string $message Mensagem de erro
     * @param int $status Código de status HTTP
     * @return array
     */
    private function handleError($message, $status)
    {
        return [
            'type' => 'HttpError',
            'error' => true,
            'message' => $message,
            'status' => $status
        ];
    }

    private function handleClientException(ClientException $e)
    {
        $responseBody = json_decode($e->getResponse()->getBody()->getContents(), true);

        return [
            'type' => 'ClientException',
            'error' => true,
            'message' => $this->translateMercadoLivreError($responseBody),
            'original' => $responseBody
        ];
    }

    private function handleRequestException(RequestException $e)
    {
        return [
            'type' => 'RequestException',
            'error' => true,
            'message' => $e->getMessage(),
        ];
    }

    private function handleGeneralException(\Exception $e)
    {
        return [
            'type' => 'GeneralException',
            'error' => true,
            'message' => $e->getMessage(),
        ];
    }

    private function translateMercadoLivreError($responseBody)
    {
        if (!isset($responseBody['error'])) {
            return 'Erro desconhecido';
        }

        $error = $responseBody['error'] ?? '';
        $message = $responseBody['message'] ?? 'Erro desconhecido';

        $translations = [
            'not_found' => 'Recurso não encontrado.',
            'forbidden' => 'Acesso negado. Verifique suas credenciais.',
            'bad_request' => 'Requisição inválida. Verifique os parâmetros enviados.',
            'invalid_token' => 'Token de acesso inválido ou expirado.',
        ];

        return $translations[$error] ?? "Erro: {$message}";
    }
}

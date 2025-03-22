<?php

namespace App\Integrations;

use Illuminate\Support\Facades\Validator;
use App\Models\Integration;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;

class ShopeeIntegration
{
    protected $client;
    protected $appId;
    protected $secret;
    protected $baseUri;

    public function __construct()
    {
        // Garante que o timezone do servidor está correto
        date_default_timezone_set('UTC');

        $settings = $this->getSettings();

        if (!$settings) {
            throw new \Exception('Credenciais da Shopee não encontradas.');
        }

        $this->appId = $settings['app_id'];
        $this->secret = $settings['secret_key'];
        $this->baseUri = 'https://open-api.affiliate.shopee.com.br/graphql';

        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    public function getIntegration()
    {
        return Integration::where('slug', 'shopee')->first();
    }

    public function getSettings()
    {
        $integration = $this->getIntegration();
        return $integration->settings ?? null;
    }

    private function generateSignature($payload, $timestamp)
    {
        // JSON corretamente formatado para assinatura
        $payloadString = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // String de assinatura seguindo a documentação
        $signatureString = $this->appId . $timestamp . $payloadString . $this->secret;

        // Hash SHA256
        return hash('sha256', $signatureString);
    }

    public function sendRequest($query, $operationName = null, $variables = [])
    {
        $payload = [
            'query' => $query,
            'operationName' => "GetProductsOffers",
            'variables' => (object) $variables
        ];

        $timestamp = time();
        $signature = $this->generateSignature($payload, $timestamp);

        $headers = [
            'Authorization' => "SHA256 Credential={$this->appId}, Timestamp={$timestamp}, Signature={$signature}",
            'Content-Type'  => 'application/json'
        ];

        try {
            $response = $this->client->request('POST', '', [
                'headers' => $headers,
                'body'    => json_encode($payload) // Garante que o corpo seja JSON válido
            ]);

            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            return $this->handleClientException($e);
        } catch (RequestException $e) {
            return $this->handleRequestException($e);
        } catch (\Exception $e) {
            return $this->handleGeneralException($e);
        }
    }

    public function getShopeeOffers($keyword = "", $sortType = 1, $page = 1, $limit = 10)
    {
        $query = <<<GQL
        query GetShopeeOffers(\$keyword: String, \$sortType: Int, \$page: Int, \$limit: Int) {
            shopeeOfferV2(keyword: \$keyword, sortType: \$sortType, page: \$page, limit: \$limit) {
                nodes {
                    commissionRate
                    imageUrl
                    offerLink
                    originalLink
                    offerName
                    offerType
                    categoryId
                    collectionId
                    periodStartTime
                    periodEndTime
                }
                pageInfo {
                    page
                    limit
                    hasNextPage
                }
            }
        }
        GQL;

        $variables = [
            "keyword" => $keyword,
            "sortType" => $sortType,
            "page" => $page,
            "limit" => $limit
        ];

        return $this->sendRequest($query, "GetShopeeOffers", $variables);
    }

    public function getProductsOffers($keyword = '', $productCatId = null, $page = 1, $limit = 10)
    {
        $query = <<<GQL
        query GetProductsOffers(\$keyword: String, \$productCatId: Int, \$page: Int, \$limit: Int) {
            productOfferV2(keyword: \$keyword, productCatId: \$productCatId, page: \$page, limit: \$limit) {
                nodes {
                    itemId
                    productCatIds
                    productName
                    imageUrl
                    priceMin
                    priceMax
                    commissionRate
                    productLink
                    offerLink
                }
                pageInfo {
                    page
                    limit
                    hasNextPage
                }
            }
        }
        GQL;
    
        // Criar array de variáveis sem incluir `productCatId` se for `null`
        $variables = [
            "keyword" => $keyword,
            "page" => $page,
            "limit" => $limit
        ];
    
        if (!is_null($productCatId)) {
            $variables["productCatId"] = (int) $productCatId; // Garante que seja um Int
        }
    
        return $this->sendRequest($query, "GetProductsOffers", $variables);
    }
    











    private function handleClientException(ClientException $e)
    {
        $responseBody = json_decode($e->getResponse()->getBody()->getContents(), true);

        return [
            'type' => 'ClientException',
            'error' => true,
            'message' => $this->translateShopeeError($responseBody),
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

    private function translateShopeeError($responseBody)
    {
        if (!isset($responseBody['errors']) || !is_array($responseBody['errors'])) {
            return 'Erro desconhecido';
        }

        $error = $responseBody['errors'][0] ?? [];
        $code = $error['extensions']['code'] ?? null;
        $message = $error['extensions']['message'] ?? 'Erro desconhecido';

        $translations = [
            10000 => "Erro interno da Shopee. Tente novamente mais tarde.",
            10010 => "Erro ao processar a requisição. Verifique a sintaxe da query ou os tipos dos campos.",
            10020 => "Erro de autenticação. Verifique sua assinatura (AppID, Secret Key e Timestamp).",
            10030 => "Limite de requisições atingido. Aguarde e tente novamente.",
            11000 => "Erro de processamento da API. Pode ser um problema de configuração dos parâmetros enviados.",
        ];

        return $translations[$code] ?? "Erro desconhecido ({$message})";
    }
}

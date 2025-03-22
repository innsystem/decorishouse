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
        // Garante que o JSON está bem formatado
        $payload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $signatureString = $this->appId . $timestamp . $payload . $this->secret;
        return hash('sha256', $signatureString);
    }

    public function sendRequest($query, $operationName = null, $variables = [])
    {
        $timestamp = time();
        $payload = [
            'query' => $query,
            'operationName' => $operationName,
            'variables' => $variables
        ];

        $signature = $this->generateSignature($payload, $timestamp);
        $headers = [
            'Authorization' => "SHA256 Credential={$this->appId}, Timestamp={$timestamp}, Signature={$signature}",
        ];

        try {
            $response = $this->client->request('POST', '', [
                'headers' => $headers,
                'json' => $payload // <-- Ajuste para enviar JSON corretamente
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

    public function getBrandOffers()
    {
        $query = "{ brandOffer { nodes { commissionRate offerName } } }";
        return $this->sendRequest($query);
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

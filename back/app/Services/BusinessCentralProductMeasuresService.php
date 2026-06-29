<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BusinessCentralProductMeasuresService
{
    protected string $tenantId;
    protected string $clientId;
    protected string $clientSecret;
    protected string $environment;
    protected string $endpoint;
    protected string $baseUrl;
    protected int $cacheTtl;

    public function __construct()
    {
        $this->tenantId = (string) config('services.business_central.tenant_id');
        $this->clientId = (string) config('services.business_central.client_id');
        $this->clientSecret = (string) config('services.business_central.client_secret');
        $this->environment = (string) config('services.business_central.environment', 'Production');
        $this->endpoint = trim((string) config('services.business_central.product_measures_endpoint', 'api/juan/app1/v2.1/pruebaCotizador'), '/');
        $this->cacheTtl = (int) config('services.business_central.product_measures_cache_ttl', 900);
        $this->baseUrl = "https://api.businesscentral.dynamics.com/v2.0/{$this->tenantId}/{$this->environment}";
    }

    public function getMeasuresBySku(string $sku): array
    {
        $sku = trim($sku);

        if ($sku === '') {
            return $this->emptyMeasures();
        }

        return Cache::remember($this->getCacheKey($sku), $this->cacheTtl, function () use ($sku) {
            try {
                if (! $this->isConfigured()) {
                    return $this->emptyMeasures();
                }

                $response = $this->request($this->endpoint, [
                    '$select' => 'sku,altura,ancho,largo',
                    '$filter' => "sku eq '{$this->escapeODataString($sku)}'",
                ]);

                $item = Arr::first($response['value'] ?? []);

                if (! is_array($item)) {
                    return $this->emptyMeasures();
                }

                return $this->normalizeMeasures($item);
            } catch (Exception $exception) {
                Log::warning('BusinessCentral product measures request failed', [
                    'sku' => $sku,
                    'message' => $exception->getMessage(),
                ]);

                return $this->emptyMeasures();
            }
        });
    }

    protected function request(string $endpoint, array $query = []): array
    {
        $response = Http::withToken($this->getAccessToken())
            ->timeout(15)
            ->retry(2, 200)
            ->get("{$this->baseUrl}/{$endpoint}", $query);

        if ($response->failed()) {
            throw new Exception('Business Central API request failed with status ' . $response->status());
        }

        return $response->json();
    }

    protected function getAccessToken(): string
    {
        return Cache::remember('bc_product_measures_access_token', 55 * 60, function () {
            $response = Http::asForm()->post("https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token", [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => 'https://api.businesscentral.dynamics.com/.default',
            ]);

            if ($response->failed()) {
                throw new Exception('Failed to obtain Business Central access token.');
            }

            return (string) $response->json('access_token');
        });
    }

    protected function normalizeMeasures(array $item): array
    {
        return [
            'width' => $this->toFloat($item['ancho'] ?? null),
            'height' => $this->toFloat($item['altura'] ?? null),
            'depth' => $this->toFloat($item['largo'] ?? null),
            'melamina_density' => null,
        ];
    }

    protected function toFloat($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    protected function getCacheKey(string $sku): string
    {
        return 'bc_product_measures_' . md5($sku);
    }

    protected function escapeODataString(string $value): string
    {
        return str_replace("'", "''", $value);
    }

    protected function isConfigured(): bool
    {
        return $this->tenantId !== ''
            && $this->clientId !== ''
            && $this->clientSecret !== '';
    }

    public function searchBySku(string $sku): ?array
    {
        $sku = trim($sku);

        if ($sku === '' || ! $this->isConfigured()) {
            return null;
        }

        return Cache::remember('bc_product_search_' . md5($sku), 300, function () use ($sku) {
            try {
                $response = $this->request($this->endpoint, [
                    '$select' => 'sku,descrpcion,description2,descripcion3,altura,ancho,largo',
                    '$filter' => "sku eq '{$this->escapeODataString($sku)}'",
                ]);

                $item = Arr::first($response['value'] ?? []);

                if (! is_array($item)) {
                    return null;
                }

                return $item;
            } catch (Exception $exception) {
                Log::warning('BusinessCentral product search request failed', [
                    'sku' => $sku,
                    'message' => $exception->getMessage(),
                ]);

                return null;
            }
        });
    }

    protected function emptyMeasures(): array
    {
        return [
            'width' => null,
            'height' => null,
            'depth' => null,
            'melamina_density' => null,
        ];
    }
}

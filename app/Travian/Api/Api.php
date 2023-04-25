<?php

namespace App\Travian\Api;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class Api
{
    private string $basePathUrl = '/test/hs/';

    private PendingRequest $client;

    public function __construct()
    {
        $api1cUrl = trim(config('services.1c.url'), '/') . $this->basePathUrl;
        $api1cLogin = config('services.1c.login');
        $api1cPassword = config('services.1c.password');

        $this->client = Http::baseUrl($api1cUrl)
            ->withBasicAuth($api1cLogin, $api1cPassword)
            ->acceptJson();
    }

    protected function queryRequest($method, $arguments = []): array
    {
        try {
            $response = $this->client
                ->retry(3, 200, throw: false)
                ->timeout(5)
                ->withoutVerifying()
                ->get($method, $arguments);
        } catch (Exception $e) {
            Log::error('Error in Travian API', ['method' => $method, 'exception' => $e->getMessage()]);

            return [];
        }

        return $this->responseHandler($response);
    }

    private function responseHandler(Response $response = null): array
    {
        if (!$response->successful()) {
            return [];
        }

        $responseJson = $response->json();

        return $responseJson ?? [];
    }
}

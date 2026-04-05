<?php

namespace Modules\TransportCard\Services;

use Illuminate\Support\Facades\Http;

class TacomApiService
{
    public function login(string $username, string $password): string
    {
        $baseUrl = config('tacom.base_url');
        $authPath = config('tacom.auth_path');
        if (!is_string($baseUrl) || !is_string($authPath)) {
            throw new \RuntimeException('Invalid Tacom API configuration: base_url and auth_path must be strings');
        }
        $url = $baseUrl.$authPath;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'username' => $username,
            'password' => $password,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to authenticate with Tacom API');
        }

        $data = $response->json();
        if (!is_array($data)) {
            throw new \RuntimeException('Invalid Tacom API response');
        }

        $accessToken = $data['access_token'] ?? null;

        if ($accessToken === null || $accessToken === '') {
            throw new \RuntimeException('Invalid Tacom API response: missing access token');
        }

        assert(is_string($accessToken));

        return $accessToken;
    }

    /**
     * @return array<string, mixed>
     */
    public function findCartao(string $accessToken, string $cardNumber, string $cpf): array
    {
        $baseUrl = config('tacom.base_url');
        $path = config('tacom.find_cartao_path');
        if (!is_string($baseUrl) || !is_string($path)) {
            throw new \RuntimeException('Invalid Tacom API configuration: base_url and find_cartao_path must be strings');
        }

        $url = "{$baseUrl}{$path}/{$cardNumber}/0/{$cpf}";

        $response = Http::withToken($accessToken)
            ->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to fetch transport card balance from Tacom API');
        }

        $data = $response->json();

        if (!is_array($data)) {
            throw new \RuntimeException('Invalid Tacom API response');
        }

        /** @var array<string, mixed> $data */
        return $data;
    }
}

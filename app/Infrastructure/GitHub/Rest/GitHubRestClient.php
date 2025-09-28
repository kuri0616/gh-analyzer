<?php

namespace App\Infrastructure\GitHub\Rest;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class GitHubRestClient
{
    private const BASE_URL = 'https://api.github.com';

    public function __construct(
        private readonly Client $client,
        private readonly string $token
    ) {
    }

    public function getJson(string $path): array
    {
        try {
            $response = $this->client->get(self::BASE_URL . $path, [
                'headers' => [
                    'Authorization' => 'token ' . $this->token,
                    'Accept' => 'application/vnd.github.v3+json',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException $e) {
            throw new RuntimeException('GitHub API request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getPaginated(string $path, int $perPage = 100): \Generator
    {
        $page = 1;
        
        do {
            $separator = str_contains($path, '?') ? '&' : '?';
            $url = $path . $separator . "per_page={$perPage}&page={$page}";
            
            $data = $this->getJson($url);
            
            if (empty($data)) {
                break;
            }
            
            yield from $data;
            
            $page++;
            
            // If we got less than requested per page, we've reached the end
        } while (count($data) === $perPage);
    }
}
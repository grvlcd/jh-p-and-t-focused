<?php

namespace App\Services\Typesense;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class TypesenseClient
{
    protected function http(): PendingRequest
    {
        $protocol = config('typesense.protocol');
        $host = config('typesense.host');
        $port = config('typesense.port');

        return Http::withHeaders([
            'X-TYPESENSE-API-KEY' => config('typesense.admin_api_key'),
        ])->baseUrl("{$protocol}://{$host}:{$port}");
    }

    public function listCollections(): Response
    {
        return $this->http()->get('/collections');
    }

    public function createCollection(array $schema): Response
    {
        return $this->http()->post('/collections', $schema);
    }

    public function upsertDocument(string $collection, array $document): Response
    {
        return $this->http()->post("/collections/{$collection}/documents", $document);
    }

    public function deleteDocument(string $collection, string $id): Response
    {
        return $this->http()->delete("/collections/{$collection}/documents/{$id}");
    }

    public function search(string $collection, string $q, array $params = []): Response
    {
        $queryParams = array_merge(['q' => $q], $params);
        return $this->http()->get("/collections/{$collection}/documents/search", $queryParams);
    }

    public function getDocument(string $collection, string $id): Response
    {
        return $this->http()->get("/collections/{$collection}/documents/{$id}");
    }

    public function getCollection(string $collection): Response
    {
        return $this->http()->get("/collections/{$collection}");
    }
}


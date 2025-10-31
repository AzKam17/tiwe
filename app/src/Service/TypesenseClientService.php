<?php

namespace App\Service;

use Typesense\Client;

class TypesenseClientService
{
    private Client $client;

    public function __construct(
        string $typesenseHost,
        string $typesensePort,
        string $typesenseApiKey
    ) {
        $this->client = new Client([
            'nodes' => [
                [
                    'host' => $typesenseHost,
                    'port' => $typesensePort,
                    'protocol' => 'http',
                ],
            ],
            'api_key' => $typesenseApiKey,
            'connection_timeout_seconds' => 2,
        ]);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getCollections()
    {
        return $this->client->collections;
    }

    public function getCollection(string $name)
    {
        return $this->client->collections[$name];
    }

    public function createCollection(array $schema): array
    {
        return $this->client->collections->create($schema);
    }

    public function deleteCollection(string $name): array
    {
        return $this->client->collections[$name]->delete();
    }
}

<?php

namespace App\Services\Typesense;

use App\Models\Protocol;
use App\Models\Thread;

class TypesenseIndexer
{
    public function __construct(
        protected TypesenseClient $client,
    ) {
    }

    public function ensureCollectionsExist(): void
    {
        $collections = $this->client->listCollections()->json() ?? [];
        $existingNames = collect($collections)->pluck('name')->all();

        $protocolsCollection = config('typesense.collections.protocols');
        $threadsCollection = config('typesense.collections.threads');

        if (! in_array($protocolsCollection, $existingNames, true)) {
            $this->client->createCollection([
                'name' => $protocolsCollection,
                'fields' => [
                    ['name' => 'id', 'type' => 'string'],
                    ['name' => 'title', 'type' => 'string'],
                    ['name' => 'tags', 'type' => 'string[]', 'facet' => true],
                    ['name' => 'votes', 'type' => 'int32'],
                ],
                'default_sorting_field' => 'votes',
            ]);
        }

        if (! in_array($threadsCollection, $existingNames, true)) {
            $this->client->createCollection([
                'name' => $threadsCollection,
                'fields' => [
                    ['name' => 'id', 'type' => 'string'],
                    ['name' => 'title', 'type' => 'string'],
                    ['name' => 'body', 'type' => 'string'],
                    ['name' => 'tags', 'type' => 'string[]', 'facet' => true],
                ],
                'default_sorting_field' => 'id',
            ]);
        }
    }

    public function upsertProtocol(Protocol $protocol): void
    {
        $this->ensureCollectionsExist();

        $collection = config('typesense.collections.protocols');

        $document = [
            'id' => (string) $protocol->getKey(),
            'title' => $protocol->title,
            'tags' => $protocol->tags ?? [],
            'votes' => $this->calculateProtocolVotes($protocol),
        ];

        $this->client->upsertDocument($collection, $document);
    }

    public function deleteProtocol(string $id): void
    {
        $collection = config('typesense.collections.protocols');

        $this->client->deleteDocument($collection, $id);
    }

    public function upsertThread(Thread $thread): void
    {
        $this->ensureCollectionsExist();

        $collection = config('typesense.collections.threads');

        $protocolTags = $thread->protocol?->tags ?? [];

        $document = [
            'id' => (string) $thread->getKey(),
            'title' => $thread->title,
            'body' => $thread->body,
            'tags' => $protocolTags,
        ];

        $this->client->upsertDocument($collection, $document);
    }

    public function deleteThread(string $id): void
    {
        $collection = config('typesense.collections.threads');

        $this->client->deleteDocument($collection, $id);
    }

    protected function calculateProtocolVotes(Protocol $protocol): int
    {
        $protocol->loadMissing([
            'threads.votes' => fn ($query) => $query->select(['id', 'votable_id', 'votable_type', 'value']),
        ]);

        return (int) $protocol->threads
            ->flatMap(fn (Thread $thread) => $thread->votes)
            ->sum('value');
    }
}


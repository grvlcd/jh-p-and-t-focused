<?php

namespace App\Console\Commands;

use App\Services\Typesense\TypesenseClient;
use Illuminate\Console\Command;

class TestTypesenseSearch extends Command
{
    protected $signature = 'typesense:test-search {query?}';

    protected $description = 'Test Typesense search functionality';

    public function handle(TypesenseClient $client): int
    {
        $query = $this->argument('query') ?? 'test';
        $collection = config('typesense.collections.protocols');

        $this->info("Testing search for: '{$query}'");
        $this->info("Collection: {$collection}\n");

        try {
            $response = $client->search($collection, $query, [
                'query_by' => 'title',
                'per_page' => 5,
            ]);

            if (!$response->successful()) {
                $this->error('Search failed');
                $this->error('Status: ' . $response->status());
                $this->error('Response: ' . $response->body());
                return Command::FAILURE;
            }

            $results = $response->json();
            $hits = $results['hits'] ?? [];
            $found = $results['found'] ?? 0;

            $this->info("Found {$found} result(s)\n");

            if (empty($hits)) {
                $this->warn('No results found');
            } else {
                foreach ($hits as $index => $hit) {
                    $doc = $hit['document'] ?? [];
                    $this->line(($index + 1) . '. ' . ($doc['title'] ?? 'No title'));
                    $this->line('   ID: ' . ($doc['id'] ?? 'N/A'));
                    if (isset($doc['tags'])) {
                        $this->line('   Tags: ' . implode(', ', $doc['tags'] ?? []));
                    }
                    $this->line('');
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

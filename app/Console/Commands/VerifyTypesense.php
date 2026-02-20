<?php

namespace App\Console\Commands;

use App\Services\Typesense\TypesenseClient;
use Illuminate\Console\Command;

class VerifyTypesense extends Command
{
    protected $signature = 'typesense:verify';

    protected $description = 'Verify Typesense connection and collections';

    public function handle(TypesenseClient $client): int
    {
        $this->info('Verifying Typesense connection...');

        // Check connection
        try {
            $response = $client->listCollections();
            
            if (!$response->successful()) {
                $this->error('Failed to connect to Typesense');
                $this->error('Status: ' . $response->status());
                $this->error('Response: ' . $response->body());
                return Command::FAILURE;
            }

            $this->info('✓ Successfully connected to Typesense');
        } catch (\Exception $e) {
            $this->error('Failed to connect to Typesense: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // List collections
        $collections = $response->json() ?? [];
        $this->info("\nCollections:");
        
        if (empty($collections)) {
            $this->warn('  No collections found');
        } else {
            foreach ($collections as $collection) {
                $name = $collection['name'] ?? 'Unknown';
                $docCount = $collection['num_documents'] ?? 0;
                $this->line("  - {$name}: {$docCount} documents");
            }
        }

        // Check expected collections
        $expectedProtocols = config('typesense.collections.protocols');
        $expectedThreads = config('typesense.collections.threads');
        
        $existingNames = collect($collections)->pluck('name')->all();
        
        $this->info("\nExpected collections:");
        $this->line("  - {$expectedProtocols}: " . (in_array($expectedProtocols, $existingNames, true) ? '✓ Exists' : '✗ Missing'));
        $this->line("  - {$expectedThreads}: " . (in_array($expectedThreads, $existingNames, true) ? '✓ Exists' : '✗ Missing'));

        // Check document counts
        $protocolsCollection = collect($collections)->firstWhere('name', $expectedProtocols);
        $threadsCollection = collect($collections)->firstWhere('name', $expectedThreads);

        if ($protocolsCollection) {
            $protocolDocs = $protocolsCollection['num_documents'] ?? 0;
            $this->info("\nProtocols indexed: {$protocolDocs}");
        }

        if ($threadsCollection) {
            $threadDocs = $threadsCollection['num_documents'] ?? 0;
            $this->info("Threads indexed: {$threadDocs}");
        }

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Protocol;
use App\Services\Typesense\TypesenseIndexer;
use Illuminate\Console\Command;

class IndexAllProtocols extends Command
{
    protected $signature = 'typesense:index-all';

    protected $description = 'Index all existing protocols and threads to Typesense';

    public function handle(TypesenseIndexer $indexer): int
    {
        $this->info('Indexing all protocols...');

        $protocols = Protocol::all();
        $count = 0;

        foreach ($protocols as $protocol) {
            try {
                $indexer->upsertProtocol($protocol);
                $count++;
                $this->line("  ✓ Indexed protocol: {$protocol->title}");
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to index protocol {$protocol->id}: " . $e->getMessage());
            }
        }

        $this->info("\nIndexed {$count} protocol(s)");

        return Command::SUCCESS;
    }
}

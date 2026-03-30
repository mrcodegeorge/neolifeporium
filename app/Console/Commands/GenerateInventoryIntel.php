<?php

namespace App\Console\Commands;

use App\Services\Admin\InventoryIntelligenceService;
use Illuminate\Console\Command;

class GenerateInventoryIntel extends Command
{
    protected $signature = 'neolifeporium:inventory-intel';
    protected $description = 'Generate inventory intelligence flags and vendor notifications';

    public function handle(InventoryIntelligenceService $service): int
    {
        $flags = $service->generate();
        $this->info("Generated {$flags->count()} inventory flags.");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Services\Admin\AdminInsightService;
use Illuminate\Console\Command;

class GenerateAdminInsights extends Command
{
    protected $signature = 'neolifeporium:insights';
    protected $description = 'Generate admin AI insights for the dashboard';

    public function handle(AdminInsightService $service): int
    {
        $insights = $service->generate();
        $this->info("Generated {$insights->count()} insights.");

        return self::SUCCESS;
    }
}

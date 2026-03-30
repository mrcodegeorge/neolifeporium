<?php

namespace App\Console\Commands;

use App\Services\Admin\ForecastService;
use Illuminate\Console\Command;

class GenerateForecasts extends Command
{
    protected $signature = 'neolifeporium:forecast';
    protected $description = 'Generate forecast snapshots for admin predictive analytics';

    public function handle(ForecastService $service): int
    {
        $revenue = $service->generateRevenueForecast();
        $users = $service->generateUserGrowthForecast();

        $count = collect([$revenue, $users])->filter()->count();
        $this->info("Generated {$count} forecast snapshots.");

        return self::SUCCESS;
    }
}

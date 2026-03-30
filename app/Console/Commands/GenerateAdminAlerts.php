<?php

namespace App\Console\Commands;

use App\Services\Admin\AdminAlertService;
use Illuminate\Console\Command;

class GenerateAdminAlerts extends Command
{
    protected $signature = 'neolifeporium:admin-alerts';
    protected $description = 'Generate smart admin alerts for anomalies and failures';

    public function handle(AdminAlertService $service): int
    {
        $alerts = $service->generate();
        $this->info("Generated {$alerts->count()} alerts.");

        return self::SUCCESS;
    }
}

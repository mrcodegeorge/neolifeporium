<?php

namespace App\Services\Insights;

use App\Models\WeatherInsight;
use Illuminate\Support\Facades\Http;

class WeatherInsightService
{
    public function latestByRegion(string $region = 'Greater Accra')
    {
        return WeatherInsight::query()
            ->where('region', $region)
            ->orderByDesc('weather_date')
            ->limit(5)
            ->get();
    }

    public function sync(string $location, string $region): ?WeatherInsight
    {
        if (! config('services.openweather.key')) {
            return null;
        }

        $response = Http::get(config('services.openweather.base_url').'/weather', [
            'q' => $location,
            'appid' => config('services.openweather.key'),
            'units' => 'metric',
        ])->json();

        return WeatherInsight::updateOrCreate(
            ['location' => $location, 'weather_date' => now()->toDateString()],
            [
                'region' => $region,
                'summary' => data_get($response, 'weather.0.description', 'Unavailable'),
                'rainfall_probability' => (int) data_get($response, 'clouds.all', 0),
                'temperature_celsius' => data_get($response, 'main.temp'),
                'alert_level' => data_get($response, 'main.temp', 0) > 34 ? 'high' : 'normal',
                'recommendations' => [
                    'Mulch vulnerable plots if afternoon heat remains high.',
                    'Prioritize irrigation tools and moisture-retention inputs.',
                ],
                'source_payload' => $response,
            ]
        );
    }
}

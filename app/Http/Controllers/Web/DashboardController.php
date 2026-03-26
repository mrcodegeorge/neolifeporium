<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardAnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardAnalyticsService $analytics) {}

    public function farmer(Request $request): View
    {
        $data = $this->analytics->farmer(
            $request->user(),
            $request->string('from')->toString() ?: null,
            $request->string('to')->toString() ?: null
        );

        return view('dashboard.farmer', ['dashboard' => $data]);
    }

    public function vendor(Request $request): View
    {
        $data = $this->analytics->vendor(
            $request->user(),
            $request->string('from')->toString() ?: null,
            $request->string('to')->toString() ?: null
        );

        return view('dashboard.vendor', ['dashboard' => $data]);
    }

    public function expert(Request $request): View
    {
        $data = $this->analytics->expert(
            $request->user(),
            $request->string('from')->toString() ?: null,
            $request->string('to')->toString() ?: null
        );

        return view('dashboard.expert', ['dashboard' => $data]);
    }

    public function admin(Request $request): View
    {
        $data = $this->analytics->admin(
            $request->user(),
            $request->string('from')->toString() ?: null,
            $request->string('to')->toString() ?: null
        );

        return view('dashboard.admin', ['dashboard' => $data]);
    }
}

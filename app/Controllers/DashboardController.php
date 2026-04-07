<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;
use DoubleE\Services\DashboardService;

class DashboardController extends BaseController
{
    private DashboardService $dashboardService;

    public function __construct(\DoubleE\Core\Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->dashboardService = new DashboardService();
    }

    /**
     * Show the main dashboard with KPIs, chart data, and recent activity.
     */
    public function index(): Response
    {
        $kpis       = $this->dashboardService->getKpis();
        $chartData  = $this->dashboardService->getRevenueExpenseChart();
        $activity   = $this->dashboardService->getRecentActivity();

        return $this->render('dashboard/index', [
            'pageTitle' => 'Dashboard',
            'kpis'      => $kpis,
            'chartData' => $chartData,
            'activity'  => $activity,
        ]);
    }
}

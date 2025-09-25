<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function reports()
    {
        return view('admin.reports.index');
    }

    public function studentReports()
    {
        return view('admin.reports.students');
    }

    public function performanceReports()
    {
        return view('admin.reports.performance');
    }

    public function riskReports()
    {
        return view('admin.reports.risk');
    }

    public function generateReport(Request $request)
    {
        return response()->json(['message' => 'Reporte generado']);
    }

    public function systemMonitoring()
    {
        return view('admin.monitoring.index');
    }

    public function usageStats()
    {
        return view('admin.monitoring.usage');
    }
}
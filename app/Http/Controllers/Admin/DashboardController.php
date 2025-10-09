<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $riskAlerts = collect([]);
    $mlAlerts = null;
    $mlAnalysis = null;
    $mlRecommendations = null;
    $overallProgress = 0;
    $learningPaths = collect([]);
    $recommendations = collect([]);
    $subjectProgress = collect([]);
    $recentActivity = collect([]);
    
    return view('admin.dashboard', compact(
        'riskAlerts',
        'mlAlerts',
        'mlAnalysis',
        'mlRecommendations',
        'overallProgress',
        'learningPaths',
        'recommendations',
        'subjectProgress',
        'recentActivity'
    ));
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
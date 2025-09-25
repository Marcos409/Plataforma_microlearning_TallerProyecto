<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('teacher.dashboard');
    }

    public function alerts()
    {
        return view('teacher.alerts.index');
    }

    public function resolveAlert(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function reports()
    {
        return view('teacher.reports.index');
    }

    public function groupReports()
    {
        return view('teacher.reports.group');
    }

    public function generateReport(Request $request)
    {
        return response()->json(['message' => 'Reporte generado']);
    }
}
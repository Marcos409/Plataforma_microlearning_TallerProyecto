<?php

namespace App\Http\Controllers\Student;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\LearningPath;
use App\Models\Recommendation;
use App\Models\RiskAlert;
use App\Models\StudentProgress;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Progreso general
        $overallProgress = $user->studentProgress()->avg('progress_percentage') ?? 0;
        
        // Rutas de aprendizaje
        $learningPaths = $user->learningPaths;
        
        // Recomendaciones pendientes
        $recommendations = $user->recommendations()
            ->where('is_completed', false)
            ->with('content')
            ->orderBy('priority')
            ->limit(5)
            ->get();

        // Alertas de riesgo activas
        $riskAlerts = $user->riskAlerts()
            ->where('is_resolved', false)
            ->orderBy('severity')
            ->orderBy('created_at', 'desc')
            ->get();

        // Progreso por materia
        $subjectProgress = $user->studentProgress()
            ->select('subject_area', 'progress_percentage', 'average_score')
            ->get()
            ->groupBy('subject_area');

        // Actividad reciente
        $recentActivity = $user->learningPaths()
            ->with(['contents' => function($query) {
                $query->where('is_completed', true)
                      ->orderBy('completed_at', 'desc')
                      ->limit(5);
            }])
            ->get()
            ->pluck('contents')
            ->flatten();

        return view('student.dashboard', compact(
            'user',
            'overallProgress', 
            'learningPaths', 
            'recommendations', 
            'riskAlerts', 
            'subjectProgress', 
            'recentActivity'
        ));
    }
}
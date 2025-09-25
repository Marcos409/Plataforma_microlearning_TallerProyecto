<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LearningPath;
use App\Models\Recommendation;
use App\Models\RiskAlert;
use App\Models\StudentProgress;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Obtener progreso general
        $overallProgress = StudentProgress::where('user_id', $user->id)
            ->avg('progress_percentage') ?? 0;

        // Rutas de aprendizaje activas
        $learningPaths = LearningPath::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('contents.content')
            ->get();

        // Recomendaciones pendientes
        $recommendations = Recommendation::where('user_id', $user->id)
            ->where('is_completed', false)
            ->with('content')
            ->orderBy('priority')
            ->limit(5)
            ->get();

        // Alertas de riesgo activas
        $riskAlerts = RiskAlert::where('user_id', $user->id)
            ->where('is_resolved', false)
            ->orderBy('severity')
            ->orderBy('created_at', 'desc')
            ->get();

        // Progreso por materia
        $subjectProgress = StudentProgress::where('user_id', $user->id)
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
            'overallProgress', 'learningPaths', 'recommendations', 
            'riskAlerts', 'subjectProgress', 'recentActivity'
        ));
    }
}
<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Obtener progreso del estudiante desde la base de datos
        $studentProgress = $user->studentProgress ?? collect([]);
        $diagnosticResults = $user->diagnosticResults ?? collect([]);
        $learningPaths = $user->learningPaths ?? collect([]);
        
        // Calcular estadísticas
        $totalActivities = $studentProgress->sum('completed_activities') ?? 0;
        $totalTimeSpent = $studentProgress->sum('total_time_spent') ?? 0;
        $overallProgress = $studentProgress->avg('progress_percentage') ?? 0;
        
        // Actividades de la última semana
        $recentActivities = $studentProgress->where('last_activity', '>=', now()->subDays(7))->sum('completed_activities') ?? 0;
        
        // Progreso por materia
        $progressBySubject = [];
        foreach ($studentProgress as $progress) {
            $subject = $progress->subject_area ?? 'Sin materia';
            if (!isset($progressBySubject[$subject])) {
                $progressBySubject[$subject] = [
                    'percentage' => 0,
                    'completed' => 0,
                    'total' => 0
                ];
            }
            $progressBySubject[$subject]['percentage'] = $progress->progress_percentage ?? 0;
            $progressBySubject[$subject]['completed'] += $progress->completed_activities ?? 0;
            $progressBySubject[$subject]['total'] += $progress->total_activities ?? 0;
        }
        
        // Si no hay datos, mostrar datos de ejemplo
        if (empty($progressBySubject)) {
            $progressBySubject = [
                'Matemáticas' => ['percentage' => 0, 'completed' => 0, 'total' => 0],
                'Física' => ['percentage' => 0, 'completed' => 0, 'total' => 0],
                'Química' => ['percentage' => 0, 'completed' => 0, 'total' => 0],
            ];
        }
        
        // Progreso reciente (últimos 5 registros)
        $recentProgress = $studentProgress->sortByDesc('last_activity')->take(5);
        
        return view('student.progress.index', compact(
            'totalActivities',
            'overallProgress', 
            'totalTimeSpent',
            'recentActivities',
            'progressBySubject',
            'recentProgress',
            'diagnosticResults',
            'learningPaths'
        ));
    }

    public function bySubject($subject)
    {
        $user = Auth::user();
        
        // Obtener progreso específico de la materia
        $subjectProgress = $user->studentProgress()
            ->where('subject_area', $subject)
            ->orderBy('last_activity', 'desc')
            ->get() ?? collect([]);
        
        // Si no hay datos reales, mostrar datos de ejemplo
        if ($subjectProgress->isEmpty()) {
            $subjectProgress = collect([
                (object)[
                    'topic' => 'Tema general',
                    'completed_activities' => 0,
                    'total_activities' => 0,
                    'progress_percentage' => 0,
                    'total_time_spent' => 0,
                    'average_score' => 0,
                    'last_activity' => null
                ]
            ]);
        }

        return view('student.progress.by-subject', compact('subject', 'subjectProgress'));
    }
}
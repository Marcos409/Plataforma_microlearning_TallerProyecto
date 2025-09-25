<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentProgress;
use App\Models\DiagnosticResponse;
use App\Models\LearningPath;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Progreso general por materia
        $subjectProgress = StudentProgress::where('user_id', $user->id)
            ->get()
            ->groupBy('subject_area');

        // Progreso en diagnósticos
        $diagnosticProgress = DiagnosticResponse::where('user_id', $user->id)
            ->with(['diagnostic', 'question'])
            ->get()
            ->groupBy('diagnostic.subject_area')
            ->map(function ($responses) {
                $totalQuestions = $responses->count();
                $correctAnswers = $responses->where('is_correct', true)->count();
                $score = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
                
                return [
                    'total_questions' => $totalQuestions,
                    'correct_answers' => $correctAnswers,
                    'score' => $score,
                    'last_attempt' => $responses->max('created_at')
                ];
            });

        // Progreso en rutas de aprendizaje
        $learningPathProgress = LearningPath::where('user_id', $user->id)
            ->with('contents')
            ->get();

        // Tiempo total estudiado
        $totalTimeSpent = StudentProgress::where('user_id', $user->id)
            ->sum('total_time_spent');

        // Actividades completadas esta semana
        $weeklyProgress = StudentProgress::where('user_id', $user->id)
            ->where('last_activity', '>=', now()->subWeek())
            ->sum('completed_activities');

        return view('student.progress.index', compact(
            'subjectProgress', 
            'diagnosticProgress', 
            'learningPathProgress',
            'totalTimeSpent',
            'weeklyProgress'
        ));
    }

    public function bySubject(Request $request, $subject)
    {
        $user = auth()->user();

        // Progreso detallado por tema en la materia
        $topicProgress = StudentProgress::where('user_id', $user->id)
            ->where('subject_area', $subject)
            ->orderBy('topic')
            ->get();

        // Diagnósticos de la materia
        $diagnosticResults = DiagnosticResponse::where('user_id', $user->id)
            ->whereHas('diagnostic', function($query) use ($subject) {
                $query->where('subject_area', $subject);
            })
            ->with(['diagnostic', 'question'])
            ->get()
            ->groupBy('diagnostic_id')
            ->map(function ($responses) {
                $diagnostic = $responses->first()->diagnostic;
                $totalQuestions = $responses->count();
                $correctAnswers = $responses->where('is_correct', true)->count();
                
                return [
                    'diagnostic' => $diagnostic,
                    'total_questions' => $totalQuestions,
                    'correct_answers' => $correctAnswers,
                    'score' => $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0,
                    'date' => $responses->first()->created_at
                ];
            });

        // Rutas de aprendizaje de la materia
        $learningPaths = LearningPath::where('user_id', $user->id)
            ->where('subject_area', $subject)
            ->with(['contents.content'])
            ->get();

        return view('student.progress.by-subject', compact(
            'subject', 
            'topicProgress', 
            'diagnosticResults', 
            'learningPaths'
        ));
    }
}
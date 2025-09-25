<?php

namespace App\Services;

use App\Models\User;
use App\Models\Diagnostic;
use App\Models\ContentLibrary;
use App\Models\Recommendation;
use App\Models\RiskAlert;
use App\Models\LearningPath;
use App\Models\StudentProgress;

class AIService
{
    public function analyzePerformanceAndRecommend(User $user, Diagnostic $diagnostic, float $score)
    {
        // Análisis básico de rendimiento
        $this->updateStudentProgress($user, $diagnostic, $score);

        // Identificar temas críticos (puntaje < 60%)
        $criticalTopics = $this->identifyCriticalTopics($user, $diagnostic);

        // Generar alertas de riesgo si es necesario
        if ($score < 50) {
            $this->generateRiskAlert($user, $diagnostic, $score, $criticalTopics);
        }

        // Generar recomendaciones de contenido
        $this->generateContentRecommendations($user, $diagnostic, $criticalTopics);

        // Crear o actualizar ruta de aprendizaje
        $this->createOrUpdateLearningPath($user, $diagnostic, $criticalTopics);
    }

    protected function updateStudentProgress(User $user, Diagnostic $diagnostic, float $score)
    {
        StudentProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'subject_area' => $diagnostic->subject_area,
                'topic' => 'Diagnóstico General'
            ],
            [
                'total_activities' => 1,
                'completed_activities' => 1,
                'progress_percentage' => $score,
                'average_score' => $score,
                'last_activity' => now()
            ]
        );
    }

    protected function identifyCriticalTopics(User $user, Diagnostic $diagnostic)
    {
        $responses = $user->diagnosticResponses()
            ->where('diagnostic_id', $diagnostic->id)
            ->with('question')
            ->get();

        $topicScores = $responses->groupBy('question.topic')->map(function ($topicResponses) {
            $correct = $topicResponses->where('is_correct', true)->count();
            $total = $topicResponses->count();
            return $total > 0 ? ($correct / $total) * 100 : 0;
        });

        return $topicScores->filter(function ($score) {
            return $score < 60; // Temas con menos del 60% de acierto
        })->keys()->toArray();
    }

    protected function generateRiskAlert(User $user, Diagnostic $diagnostic, float $score, array $criticalTopics)
    {
        RiskAlert::create([
            'user_id' => $user->id,
            'type' => 'low_performance',
            'title' => 'Bajo rendimiento en diagnóstico',
            'description' => "El estudiante obtuvo {$score}% en el diagnóstico de {$diagnostic->subject_area}",
            'severity' => $score < 30 ? 'critical' : ($score < 40 ? 'high' : 'medium'),
            'affected_topics' => $criticalTopics,
        ]);
    }

    protected function generateContentRecommendations(User $user, Diagnostic $diagnostic, array $criticalTopics)
    {
        foreach ($criticalTopics as $topic) {
            $contents = ContentLibrary::where('subject_area', $diagnostic->subject_area)
                ->where('topic', $topic)
                ->where('difficulty_level', '<=', 2) // Contenido básico/intermedio
                ->where('active', true)
                ->limit(3)
                ->get();

            foreach ($contents as $content) {
                Recommendation::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'content_id' => $content->id,
                    ],
                    [
                        'type' => 'diagnostic_based',
                        'reason' => "Refuerzo necesario en el tema: {$topic}",
                        'priority' => 1, // Alta prioridad
                    ]
                );
            }
        }
    }

    protected function createOrUpdateLearningPath(User $user, Diagnostic $diagnostic, array $criticalTopics)
    {
        $pathName = "Refuerzo en {$diagnostic->subject_area}";
        
        $learningPath = LearningPath::firstOrCreate(
            [
                'user_id' => $user->id,
                'subject_area' => $diagnostic->subject_area,
            ],
            [
                'name' => $pathName,
                'description' => "Ruta personalizada basada en el diagnóstico inicial",
                'critical_topics' => $criticalTopics,
                'status' => 'active'
            ]
        );

        // Agregar contenidos recomendados a la ruta
        $order = 1;
        foreach ($criticalTopics as $topic) {
            $contents = ContentLibrary::where('subject_area', $diagnostic->subject_area)
                ->where('topic', $topic)
                ->where('active', true)
                ->orderBy('difficulty_level')
                ->get();

            foreach ($contents as $content) {
                $learningPath->contents()->firstOrCreate(
                    ['content_id' => $content->id],
                    ['order_sequence' => $order++]
                );
            }
        }

        $learningPath->updateProgress();
    }

    public function predictDifficulties(User $user)
    {
        // Algoritmo simple de predicción basado en patrones
        $progress = StudentProgress::where('user_id', $user->id)->get();

        foreach ($progress as $subjectProgress) {
            // Regla 1: Si el progreso es < 30% y no hay actividad en 7 días
            if ($subjectProgress->progress_percentage < 30 && 
                $subjectProgress->last_activity < now()->subDays(7)) {
                
                RiskAlert::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'type' => 'inactivity',
                        'is_resolved' => false
                    ],
                    [
                        'title' => 'Inactividad prolongada',
                        'description' => "Sin actividad en {$subjectProgress->subject_area} por más de 7 días",
                        'severity' => 'medium',
                        'affected_topics' => [$subjectProgress->topic]
                    ]
                );
            }

            // Regla 2: Si el puntaje promedio es muy bajo
            if ($subjectProgress->average_score < 40) {
                RiskAlert::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'type' => 'low_performance',
                        'is_resolved' => false
                    ],
                    [
                        'title' => 'Rendimiento académico bajo',
                        'description' => "Puntaje promedio de {$subjectProgress->average_score}% en {$subjectProgress->subject_area}",
                        'severity' => $subjectProgress->average_score < 25 ? 'critical' : 'high',
                        'affected_topics' => $subjectProgress->weak_areas ?? []
                    ]
                );
            }
        }
    }
}
<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Diagnostic;
use App\Models\DiagnosticResponse;
use App\Services\AIService;
use Illuminate\Http\Request;

class DiagnosticController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        $diagnostics = Diagnostic::where('active', true)->get();
        $completedDiagnostics = DiagnosticResponse::where('user_id', auth()->id())
            ->pluck('diagnostic_id')
            ->unique();

        return view('student.diagnostics.index', compact('diagnostics', 'completedDiagnostics'));
    }

    public function show(Diagnostic $diagnostic)
    {
        $diagnostic->load('questions');
        $hasCompleted = DiagnosticResponse::where('user_id', auth()->id())
            ->where('diagnostic_id', $diagnostic->id)
            ->exists();

        if ($hasCompleted) {
            return redirect()->route('student.diagnostics.result', $diagnostic);
        }

        return view('student.diagnostics.show', compact('diagnostic'));
    }

    public function submit(Request $request, Diagnostic $diagnostic)
    {
        $request->validate([
            'responses' => 'required|array',
            'responses.*' => 'required|integer',
            'time_spent' => 'required|array',
            'time_spent.*' => 'required|integer'
        ]);

        $diagnostic->load('questions');
        $correctAnswers = 0;
        $totalQuestions = $diagnostic->questions->count();

        foreach ($request->responses as $questionId => $selectedAnswer) {
            $question = $diagnostic->questions->find($questionId);
            $isCorrect = $question->correct_answer == $selectedAnswer;
            
            if ($isCorrect) $correctAnswers++;

            DiagnosticResponse::create([
                'user_id' => auth()->id(),
                'diagnostic_id' => $diagnostic->id,
                'question_id' => $questionId,
                'selected_answer' => $selectedAnswer,
                'is_correct' => $isCorrect,
                'time_spent' => $request->time_spent[$questionId] ?? 0
            ]);
        }

        // Calcular puntaje y generar recomendaciones con IA
        $score = ($correctAnswers / $totalQuestions) * 100;
        $this->aiService->analyzePerformanceAndRecommend(auth()->user(), $diagnostic, $score);

        return redirect()->route('student.diagnostics.result', $diagnostic);
    }

    public function result(Diagnostic $diagnostic)
    {
        $responses = DiagnosticResponse::where('user_id', auth()->id())
            ->where('diagnostic_id', $diagnostic->id)
            ->with('question')
            ->get();

        $correctAnswers = $responses->where('is_correct', true)->count();
        $totalQuestions = $responses->count();
        $score = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;

        // Agrupar por temas para análisis
        $topicAnalysis = $responses->groupBy('question.topic')->map(function ($topicResponses) {
            $correct = $topicResponses->where('is_correct', true)->count();
            $total = $topicResponses->count();
            return [
                'correct' => $correct,
                'total' => $total,
                'percentage' => $total > 0 ? ($correct / $total) * 100 : 0
            ];
        });

        return view('student.diagnostics.result', compact(
            'diagnostic', 'responses', 'correctAnswers', 'totalQuestions', 
            'score', 'topicAnalysis'
        ));
    }
}
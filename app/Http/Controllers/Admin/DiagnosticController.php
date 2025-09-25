<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Diagnostic;
use App\Models\DiagnosticQuestion;
use Illuminate\Http\Request;

class DiagnosticController extends Controller
{
    public function index()
    {
        $diagnostics = Diagnostic::withCount('questions')->orderBy('created_at', 'desc')->get();
        
        return view('admin.diagnostics.index', compact('diagnostics'));
    }

    public function create()
    {
        return view('admin.diagnostics.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_area' => 'required|string|max:255',
            'passing_score' => 'required|integer|min:1|max:100',
        ]);

        $diagnostic = Diagnostic::create($request->all());

        return redirect()->route('admin.diagnostics.questions.index', $diagnostic)
            ->with('success', 'Diagnóstico creado. Ahora agrega las preguntas.');
    }

    public function show(Diagnostic $diagnostic)
    {
        $diagnostic->load('questions');
        
        return view('admin.diagnostics.show', compact('diagnostic'));
    }

    public function edit(Diagnostic $diagnostic)
    {
        return view('admin.diagnostics.edit', compact('diagnostic'));
    }

    public function update(Request $request, Diagnostic $diagnostic)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_area' => 'required|string|max:255',
            'passing_score' => 'required|integer|min:1|max:100',
            'active' => 'boolean',
        ]);

        $diagnostic->update($request->all());

        return redirect()->route('admin.diagnostics.index')
            ->with('success', 'Diagnóstico actualizado exitosamente.');
    }

    public function destroy(Diagnostic $diagnostic)
    {
        $diagnostic->delete();

        return redirect()->route('admin.diagnostics.index')
            ->with('success', 'Diagnóstico eliminado exitosamente.');
    }

    // Gestión de preguntas
    public function questionsIndex(Diagnostic $diagnostic)
    {
        $questions = $diagnostic->questions()->orderBy('created_at')->get();
        
        return view('admin.diagnostics.questions.index', compact('diagnostic', 'questions'));
    }

    public function questionsCreate(Diagnostic $diagnostic)
    {
        return view('admin.diagnostics.questions.create', compact('diagnostic'));
    }

    public function questionsStore(Request $request, Diagnostic $diagnostic)
    {
        $request->validate([
            'question' => 'required|string',
            'options' => 'required|array|min:2|max:5',
            'options.*' => 'required|string',
            'correct_answer' => 'required|integer|min:0',
            'difficulty_level' => 'required|integer|min:1|max:3',
            'topic' => 'required|string|max:255',
        ]);

        // Validar que la respuesta correcta esté dentro del rango de opciones
        if ($request->correct_answer >= count($request->options)) {
            return back()->withErrors(['correct_answer' => 'La respuesta correcta debe estar dentro del rango de opciones.']);
        }

        DiagnosticQuestion::create([
            'diagnostic_id' => $diagnostic->id,
            'question' => $request->question,
            'options' => array_values($request->options), // Reindexar array
            'correct_answer' => $request->correct_answer,
            'difficulty_level' => $request->difficulty_level,
            'topic' => $request->topic,
        ]);

        $diagnostic->updateTotalQuestions();

        return redirect()->route('admin.diagnostics.questions.index', $diagnostic)
            ->with('success', 'Pregunta agregada exitosamente.');
    }

    public function questionsEdit(Diagnostic $diagnostic, DiagnosticQuestion $question)
    {
        return view('admin.diagnostics.questions.edit', compact('diagnostic', 'question'));
    }

    public function questionsUpdate(Request $request, Diagnostic $diagnostic, DiagnosticQuestion $question)
    {
        $request->validate([
            'question' => 'required|string',
            'options' => 'required|array|min:2|max:5',
            'options.*' => 'required|string',
            'correct_answer' => 'required|integer|min:0',
            'difficulty_level' => 'required|integer|min:1|max:3',
            'topic' => 'required|string|max:255',
        ]);

        if ($request->correct_answer >= count($request->options)) {
            return back()->withErrors(['correct_answer' => 'La respuesta correcta debe estar dentro del rango de opciones.']);
        }

        $question->update([
            'question' => $request->question,
            'options' => array_values($request->options),
            'correct_answer' => $request->correct_answer,
            'difficulty_level' => $request->difficulty_level,
            'topic' => $request->topic,
        ]);

        return redirect()->route('admin.diagnostics.questions.index', $diagnostic)
            ->with('success', 'Pregunta actualizada exitosamente.');
    }

    public function questionsDestroy(Diagnostic $diagnostic, DiagnosticQuestion $question)
    {
        $question->delete();
        $diagnostic->updateTotalQuestions();

        return redirect()->route('admin.diagnostics.questions.index', $diagnostic)
            ->with('success', 'Pregunta eliminada exitosamente.');
    }
}
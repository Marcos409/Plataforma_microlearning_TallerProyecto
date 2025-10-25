<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\DiagnosticDAOInterface; // 👈 Importamos la Interfaz
use App\Http\Controllers\Controller;
use App\Models\Diagnostic; // Se mantiene para Type Hinting de rutas
use App\Models\DiagnosticQuestion; // Se mantiene para Type Hinting de rutas
use Illuminate\Http\Request;

class DiagnosticController extends Controller
{
    protected DiagnosticDAOInterface $diagnosticDAO; // 👈 Usamos la Interfaz

    // Inyección de dependencia
    public function __construct(DiagnosticDAOInterface $diagnosticDAO)
    {
        $this->diagnosticDAO = $diagnosticDAO;
    }

    public function index()
    {
        // 1. Usar DAO para obtener todos los diagnósticos con conteo de preguntas
        $diagnostics = $this->diagnosticDAO->getAllWithQuestionCount();
        
        return view('admin.diagnostics.index', compact('diagnostics'));
    }

    public function create()
    {
        return view('admin.diagnostics.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_area' => 'required|string|max:255',
            'passing_score' => 'required|integer|min:1|max:100',
        ]);

        // 2. Usar DAO para crear el diagnóstico
        $diagnostic = $this->diagnosticDAO->createDiagnostic($validated);

        return redirect()->route('admin.diagnostics.questions.index', $diagnostic)
            ->with('success', 'Diagnóstico creado. Ahora agrega las preguntas.');
    }

    public function show(Diagnostic $diagnostic)
    {
        // Nota: En la vista se debe cargar la relación 'questions', si no lo hace,
        // se podría usar $this->diagnosticDAO->findWithQuestions($diagnostic->id);
        
        return view('admin.diagnostics.show', compact('diagnostic'));
    }

    public function edit(Diagnostic $diagnostic)
    {
        return view('admin.diagnostics.edit', compact('diagnostic'));
    }

    public function update(Request $request, Diagnostic $diagnostic)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_area' => 'required|string|max:255',
            'passing_score' => 'required|integer|min:1|max:100',
            'active' => 'nullable|boolean',
        ]);
        
        $validated['active'] = $request->has('active');
        
        // 3. Usar DAO para actualizar el diagnóstico
        $this->diagnosticDAO->updateDiagnostic($diagnostic, $validated);

        return redirect()->route('admin.diagnostics.index')
            ->with('success', 'Diagnóstico actualizado exitosamente.');
    }

    public function destroy(Diagnostic $diagnostic)
    {
        // 4. Usar DAO para eliminar el diagnóstico
        $this->diagnosticDAO->deleteDiagnostic($diagnostic);

        return redirect()->route('admin.diagnostics.index')
            ->with('success', 'Diagnóstico eliminado exitosamente.');
    }

    // --- Gestión de preguntas (Questions) ---

    public function questionsIndex(Diagnostic $diagnostic)
    {
        // 5. Usar DAO para obtener las preguntas del diagnóstico
        $questions = $this->diagnosticDAO->getQuestionsForDiagnostic($diagnostic);
        
        return view('admin.diagnostics.questions.index', compact('diagnostic', 'questions'));
    }

    public function questionsCreate(Diagnostic $diagnostic)
    {
        return view('admin.diagnostics.questions.create', compact('diagnostic'));
    }

    public function questionsStore(Request $request, Diagnostic $diagnostic)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'options' => 'required|array|min:2|max:5',
            'options.*' => 'required|string',
            'correct_answer' => 'required|integer|min:0',
            'difficulty_level' => 'required|integer|min:1|max:3',
            'topic' => 'required|string|max:255',
        ]);

        // Validar que la respuesta correcta esté dentro del rango de opciones
        if ($validated['correct_answer'] >= count($validated['options'])) {
            return back()->withErrors(['correct_answer' => 'La respuesta correcta debe estar dentro del rango de opciones.']);
        }

        // 6. Usar DAO para crear la pregunta
        $this->diagnosticDAO->createQuestion($diagnostic, [
            'question' => $validated['question'],
            'options' => array_values($validated['options']), // Reindexar
            'correct_answer' => $validated['correct_answer'],
            'difficulty_level' => $validated['difficulty_level'],
            'topic' => $validated['topic'],
        ]);

        // 7. Usar DAO para actualizar el conteo total
        $this->diagnosticDAO->syncTotalQuestions($diagnostic);

        return redirect()->route('admin.diagnostics.questions.index', $diagnostic)
            ->with('success', 'Pregunta agregada exitosamente.');
    }

    public function questionsEdit(Diagnostic $diagnostic, DiagnosticQuestion $question)
    {
        return view('admin.diagnostics.questions.edit', compact('diagnostic', 'question'));
    }

    public function questionsUpdate(Request $request, Diagnostic $diagnostic, DiagnosticQuestion $question)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'options' => 'required|array|min:2|max:5',
            'options.*' => 'required|string',
            'correct_answer' => 'required|integer|min:0',
            'difficulty_level' => 'required|integer|min:1|max:3',
            'topic' => 'required|string|max:255',
        ]);

        if ($validated['correct_answer'] >= count($validated['options'])) {
            return back()->withErrors(['correct_answer' => 'La respuesta correcta debe estar dentro del rango de opciones.']);
        }

        // 8. Usar DAO para actualizar la pregunta
        $this->diagnosticDAO->updateQuestion($question, [
            'question' => $validated['question'],
            'options' => array_values($validated['options']),
            'correct_answer' => $validated['correct_answer'],
            'difficulty_level' => $validated['difficulty_level'],
            'topic' => $validated['topic'],
        ]);

        return redirect()->route('admin.diagnostics.questions.index', $diagnostic)
            ->with('success', 'Pregunta actualizada exitosamente.');
    }

    public function questionsDestroy(Diagnostic $diagnostic, DiagnosticQuestion $question)
    {
        // 9. Usar DAO para eliminar la pregunta
        $this->diagnosticDAO->deleteQuestion($question);
        
        // 10. Usar DAO para actualizar el conteo total
        $this->diagnosticDAO->syncTotalQuestions($diagnostic);

        return redirect()->route('admin.diagnostics.questions.index', $diagnostic)
            ->with('success', 'Pregunta eliminada exitosamente.');
    }
}

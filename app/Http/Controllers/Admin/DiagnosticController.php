<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataAccessModels\DiagnosticoModel;
use Illuminate\Http\Request;

class DiagnosticController extends Controller
{
    protected $diagnosticoModel;

    /**
     * Constructor - Inyección del modelo PDO
     */
    public function __construct()
    {
        $this->diagnosticoModel = new DiagnosticoModel();
    }

    /**
     * Listar todos los diagnósticos
     */
    public function index()
    {
        // Obtener diagnósticos usando SP
        $diagnostics = $this->diagnosticoModel->listarDiagnosticos();
        
        return view('admin.diagnostics.index', [
            'diagnostics' => $diagnostics
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('admin.diagnostics.create');
    }

    /**
     * Crear nuevo diagnóstico
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_area' => 'required|string|max:255',
            'difficulty_level' => 'required|in:Básico,Intermedio,Avanzado',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'passing_score' => 'required|numeric|min:1|max:100',
        ]);

        // Crear diagnóstico usando SP
        $diagnosticId = $this->diagnosticoModel->crearDiagnostico(
            $validated['title'],
            $validated['description'] ?? null,
            $validated['subject_area'],
            $validated['difficulty_level'],
            $validated['time_limit_minutes'] ?? null,
            $validated['passing_score']
        );

        if (!$diagnosticId) {
            return redirect()->back()
                ->with('error', 'Error al crear el diagnóstico')
                ->withInput();
        }

        return redirect()->route('admin.diagnostics.questions.index', $diagnosticId)
            ->with('success', 'Diagnóstico creado. Ahora agrega las preguntas.');
    }

    /**
     * Mostrar un diagnóstico específico
     */
    public function show($id)
    {
        // Obtener diagnóstico completo con preguntas usando SP
        $data = $this->diagnosticoModel->obtenerDiagnosticoCompleto($id);

        if (!$data) {
            abort(404, 'Diagnóstico no encontrado');
        }

        return view('admin.diagnostics.show', [
            'diagnostic' => $data['diagnostico'],
            'questions' => $data['preguntas']
        ]);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        // Obtener diagnóstico usando SP
        $diagnostic = $this->diagnosticoModel->obtenerDiagnostico($id);

        if (!$diagnostic) {
            abort(404, 'Diagnóstico no encontrado');
        }

        return view('admin.diagnostics.edit', [
            'diagnostic' => $diagnostic
        ]);
    }

    /**
     * Actualizar diagnóstico
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_area' => 'required|string|max:255',
            'difficulty_level' => 'required|in:Básico,Intermedio,Avanzado',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'passing_score' => 'required|numeric|min:1|max:100',
            'active' => 'nullable|boolean',
        ]);

        $active = $request->has('active') ? 1 : 0;

        // Actualizar diagnóstico usando SP
        $updated = $this->diagnosticoModel->actualizarDiagnostico(
            $id,
            $validated['title'],
            $validated['description'] ?? null,
            $validated['subject_area'],
            $validated['difficulty_level'],
            $validated['time_limit_minutes'] ?? null,
            $validated['passing_score'],
            $active
        );

        if (!$updated) {
            return redirect()->back()
                ->with('error', 'Error al actualizar el diagnóstico')
                ->withInput();
        }

        return redirect()->route('admin.diagnostics.index')
            ->with('success', 'Diagnóstico actualizado exitosamente.');
    }

    /**
     * Eliminar diagnóstico
     */
    public function destroy($id)
    {
        // Eliminar diagnóstico usando SP
        $deleted = $this->diagnosticoModel->eliminarDiagnostico($id);

        if (!$deleted) {
            return redirect()->back()
                ->with('error', 'Error al eliminar el diagnóstico');
        }

        return redirect()->route('admin.diagnostics.index')
            ->with('success', 'Diagnóstico eliminado exitosamente.');
    }

    // ==================== GESTIÓN DE PREGUNTAS ====================

    /**
     * Listar preguntas de un diagnóstico
     */
    public function questionsIndex($diagnosticId)
    {
        // Obtener diagnóstico con preguntas usando SP
        $data = $this->diagnosticoModel->obtenerDiagnosticoCompleto($diagnosticId);

        if (!$data) {
            abort(404, 'Diagnóstico no encontrado');
        }

        return view('admin.diagnostics.questions.index', [
            'diagnostic' => $data['diagnostico'],
            'questions' => $data['preguntas']
        ]);
    }

    /**
     * Mostrar formulario para crear pregunta
     */
    public function questionsCreate($diagnosticId)
    {
        // Verificar que el diagnóstico existe
        $diagnostic = $this->diagnosticoModel->obtenerDiagnostico($diagnosticId);

        if (!$diagnostic) {
            abort(404, 'Diagnóstico no encontrado');
        }

        return view('admin.diagnostics.questions.create', [
            'diagnostic' => $diagnostic
        ]);
    }

    /**
     * Crear nueva pregunta
     */
    public function questionsStore(Request $request, $diagnosticId)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,open_ended',
            'options' => 'nullable|array|min:2|max:5',
            'options.*' => 'required|string',
            'correct_answer' => 'required|string',
            'points' => 'nullable|numeric|min:0.5|max:10',
        ]);

        // Validar que la respuesta correcta esté en las opciones (para multiple_choice)
        if ($validated['question_type'] === 'multiple_choice' && isset($validated['options'])) {
            if (!\in_array($validated['correct_answer'], $validated['options'])) {
                return redirect()->back()
                    ->withErrors(['correct_answer' => 'La respuesta correcta debe estar entre las opciones'])
                    ->withInput();
            }
        }

        // Crear pregunta usando SP
        $questionId = $this->diagnosticoModel->crearPregunta(
            $diagnosticId,
            $validated['question_text'],
            $validated['question_type'],
            isset($validated['options']) ? \json_encode($validated['options']) : null,
            $validated['correct_answer'],
            $validated['points'] ?? 1.0
        );

        if (!$questionId) {
            return redirect()->back()
                ->with('error', 'Error al crear la pregunta')
                ->withInput();
        }

        return redirect()->route('admin.diagnostics.questions.index', $diagnosticId)
            ->with('success', 'Pregunta agregada exitosamente.');
    }

    /**
     * Mostrar formulario de edición de pregunta
     */
    public function questionsEdit($diagnosticId, $questionId)
    {
        // Obtener diagnóstico
        $diagnostic = $this->diagnosticoModel->obtenerDiagnostico($diagnosticId);

        if (!$diagnostic) {
            abort(404, 'Diagnóstico no encontrado');
        }

        // Obtener pregunta
        $question = $this->diagnosticoModel->obtenerPregunta($questionId);

        if (!$question) {
            abort(404, 'Pregunta no encontrada');
        }

        return view('admin.diagnostics.questions.edit', [
            'diagnostic' => $diagnostic,
            'question' => $question
        ]);
    }

    /**
     * Actualizar pregunta
     */
    public function questionsUpdate(Request $request, $diagnosticId, $questionId)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,open_ended',
            'options' => 'nullable|array|min:2|max:5',
            'options.*' => 'required|string',
            'correct_answer' => 'required|string',
            'points' => 'nullable|numeric|min:0.5|max:10',
        ]);

        // Validar respuesta correcta
        if ($validated['question_type'] === 'multiple_choice' && isset($validated['options'])) {
            if (!\in_array($validated['correct_answer'], $validated['options'])) {
                return redirect()->back()
                    ->withErrors(['correct_answer' => 'La respuesta correcta debe estar entre las opciones'])
                    ->withInput();
            }
        }

        // Actualizar pregunta usando SP
        $updated = $this->diagnosticoModel->actualizarPregunta(
            $questionId,
            $validated['question_text'],
            $validated['question_type'],
            isset($validated['options']) ? \json_encode($validated['options']) : null,
            $validated['correct_answer'],
            $validated['points'] ?? 1.0
        );

        if (!$updated) {
            return redirect()->back()
                ->with('error', 'Error al actualizar la pregunta')
                ->withInput();
        }

        return redirect()->route('admin.diagnostics.questions.index', $diagnosticId)
            ->with('success', 'Pregunta actualizada exitosamente.');
    }

    /**
     * Eliminar pregunta
     */
    public function questionsDestroy($diagnosticId, $questionId)
    {
        // Eliminar pregunta usando SP
        $deleted = $this->diagnosticoModel->eliminarPregunta($questionId);

        if (!$deleted) {
            return redirect()->back()
                ->with('error', 'Error al eliminar la pregunta');
        }

        return redirect()->route('admin.diagnostics.questions.index', $diagnosticId)
            ->with('success', 'Pregunta eliminada exitosamente.');
    }
}
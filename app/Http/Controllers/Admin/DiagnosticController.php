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
            'difficulty_level' => 'nullable|in:Básico,Intermedio,Avanzado',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'passing_score' => 'required|numeric|min:1|max:100',
        ]);

        $validated['difficulty_level'] = $validated['difficulty_level'] ?? 'Básico';

        $diagnosticId = $this->diagnosticoModel->crearDiagnostico($validated);

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
        $data = $this->diagnosticoModel->obtenerDiagnosticoCompleto($id);

        if (!$data) {
            abort(404, 'Diagnóstico no encontrado');
        }

        $diagnosticData = $data['diagnostico'];
        if (isset($diagnosticData[0]) && is_array($diagnosticData[0])) {
            $diagnosticData = $diagnosticData[0];
        }

        return view('admin.diagnostics.show', [
            'diagnostic' => (object) $diagnosticData,
            'questions' => collect($data['preguntas'])->map(fn($q) => (object) $q)
        ]);
    }

    /**
 * Mostrar formulario de edición
 */
public function edit($id)
{
    $diagnostic = $this->diagnosticoModel->obtenerDiagnostico($id);

    if (!$diagnostic) {
        abort(404, 'Diagnóstico no encontrado');
    }

    // ✅ CONVERTIR A OBJETO si es array
    if (is_array($diagnostic)) {
        // Si es un array de arrays, tomar el primero
        if (isset($diagnostic[0]) && is_array($diagnostic[0])) {
            $diagnostic = (object) $diagnostic[0];
        } else {
            $diagnostic = (object) $diagnostic;
        }
    }

    return view('admin.diagnostics.edit', [
        'diagnostic' => $diagnostic,
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
            'difficulty_level' => 'nullable|in:Básico,Intermedio,Avanzado',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'passing_score' => 'required|numeric|min:1|max:100',
            'active' => 'nullable|boolean',
        ]);

        $validated['difficulty_level'] = $validated['difficulty_level'] ?? 'Básico';
        $validated['active'] = $request->has('active') ? 1 : 0;

        $updated = $this->diagnosticoModel->actualizarDiagnostico($id, $validated);

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
    $data = $this->diagnosticoModel->obtenerDiagnosticoCompleto($diagnosticId);

    if (!$data) {
        abort(404, 'Diagnóstico no encontrado');
    }

    $diagnosticData = $data['diagnostico'];
    if (isset($diagnosticData[0]) && is_array($diagnosticData[0])) {
        $diagnosticData = $diagnosticData[0];
    }

    // ✅ MANEJO ROBUSTO DE PREGUNTAS
    $preguntas = $data['preguntas'] ?? [];
    
    // Si es string (JSON), decodificar
    if (is_string($preguntas)) {
        $decoded = json_decode($preguntas, true);
        $preguntas = is_array($decoded) ? $decoded : [];
    }
    
    // Si no es array, usar array vacío
    if (!is_array($preguntas)) {
        $preguntas = [];
    }

    // Si las preguntas tienen 'options' como string JSON, decodificarlas
    $preguntas = array_map(function($pregunta) {
        if (is_array($pregunta) && isset($pregunta['options']) && is_string($pregunta['options'])) {
            $pregunta['options'] = json_decode($pregunta['options'], true);
        }
        return $pregunta;
    }, $preguntas);

    return view('admin.diagnostics.questions.index', [
        'diagnostic' => (object) $diagnosticData,
        'questions' => collect($preguntas)->map(fn($q) => (object) $q)
    ]);
}

    /**
     * Mostrar formulario para crear pregunta
     */
    public function questionsCreate($diagnosticId)
    {
        $diagnostic = $this->diagnosticoModel->obtenerDiagnostico($diagnosticId);

        if (!$diagnostic) {
            abort(404, 'Diagnóstico no encontrado');
        }

        return view('admin.diagnostics.questions.create', [
            'diagnostic' => (object) $diagnostic
        ]);
    }

    /**
     * Crear nueva pregunta
     */
    public function questionsStore(Request $request, $diagnosticId)
    {
        // ✅ VALIDACIÓN AJUSTADA para coincidir con el formulario
        $validated = $request->validate([
            'question_text' => 'required|string',  // Cambio de 'question' a 'question_text'
            'question_type' => 'nullable|in:multiple_choice,true_false,open_ended',
            'topic' => 'nullable|string',
            'difficulty_level' => 'nullable|integer|min:1|max:3',
            'options' => 'nullable|array|min:2|max:5',
            'options.*' => 'nullable|string',
            'correct_answer' => 'required',  // Acepta índice
            'points' => 'nullable|numeric|min:0.5|max:10',
        ]);

        // Establecer tipo por defecto
        $validated['question_type'] = $validated['question_type'] ?? 'multiple_choice';

        // ✅ Filtrar opciones vacías
        if (isset($validated['options'])) {
            $validated['options'] = array_values(array_filter($validated['options'], function($opt) {
                return !empty(trim($opt));
            }));
        }

        // ✅ Convertir correct_answer de índice a texto de la opción
        if (isset($validated['options']) && is_numeric($validated['correct_answer'])) {
            $correctIndex = (int) $validated['correct_answer'];
            if (isset($validated['options'][$correctIndex])) {
                $validated['correct_answer'] = $validated['options'][$correctIndex];
            }
        }

        // Validar que la respuesta correcta no esté vacía
        if (empty(trim($validated['correct_answer']))) {
            return redirect()->back()
                ->withErrors(['correct_answer' => 'Debe seleccionar una respuesta correcta válida'])
                ->withInput();
        }

        // Validar mínimo 2 opciones para multiple_choice
        if ($validated['question_type'] === 'multiple_choice' && count($validated['options']) < 2) {
            return redirect()->back()
                ->withErrors(['options' => 'Debe proporcionar al menos 2 opciones'])
                ->withInput();
        }

        // ✅ Crear pregunta usando array
        $questionId = $this->diagnosticoModel->crearPregunta([
            'diagnostic_id' => $diagnosticId,
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'options' => isset($validated['options']) ? json_encode($validated['options']) : null,
            'correct_answer' => $validated['correct_answer'],
            'points' => $validated['points'] ?? 1.0
        ]);

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
        $diagnostic = $this->diagnosticoModel->obtenerDiagnostico($diagnosticId);

        if (!$diagnostic) {
            abort(404, 'Diagnóstico no encontrado');
        }

        $question = $this->diagnosticoModel->obtenerPregunta($questionId);

        if (!$question) {
            abort(404, 'Pregunta no encontrada');
        }

        return view('admin.diagnostics.questions.edit', [
            'diagnostic' => (object) $diagnostic,
            'question' => (object) $question
        ]);
    }

    /**
     * Actualizar pregunta
     */
    public function questionsUpdate(Request $request, $diagnosticId, $questionId)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'nullable|in:multiple_choice,true_false,open_ended',
            'topic' => 'nullable|string',
            'difficulty_level' => 'nullable|integer|min:1|max:3',
            'options' => 'nullable|array|min:2|max:5',
            'options.*' => 'nullable|string',
            'correct_answer' => 'required',
            'points' => 'nullable|numeric|min:0.5|max:10',
        ]);

        $validated['question_type'] = $validated['question_type'] ?? 'multiple_choice';

        // Filtrar opciones vacías
        if (isset($validated['options'])) {
            $validated['options'] = array_values(array_filter($validated['options'], function($opt) {
                return !empty(trim($opt));
            }));
        }

        // Convertir correct_answer de índice a texto
        if (isset($validated['options']) && is_numeric($validated['correct_answer'])) {
            $correctIndex = (int) $validated['correct_answer'];
            if (isset($validated['options'][$correctIndex])) {
                $validated['correct_answer'] = $validated['options'][$correctIndex];
            }
        }

        // Validar respuesta correcta
        if ($validated['question_type'] === 'multiple_choice' && isset($validated['options'])) {
            if (!in_array($validated['correct_answer'], $validated['options'])) {
                return redirect()->back()
                    ->withErrors(['correct_answer' => 'La respuesta correcta debe estar entre las opciones'])
                    ->withInput();
            }
        }

        // ✅ Actualizar usando array
        $updated = $this->diagnosticoModel->actualizarPregunta($questionId, [
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'options' => isset($validated['options']) ? json_encode($validated['options']) : null,
            'correct_answer' => $validated['correct_answer'],
            'points' => $validated['points'] ?? 1.0
        ]);

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
        $deleted = $this->diagnosticoModel->eliminarPregunta($questionId);

        if (!$deleted) {
            return redirect()->back()
                ->with('error', 'Error al eliminar la pregunta');
        }

        return redirect()->route('admin.diagnostics.questions.index', $diagnosticId)
            ->with('success', 'Pregunta eliminada exitosamente.');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\DataAccessModels\ContenidoModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    protected $contenidoModel;

    // Mapeo de dificultad: texto a valor para la BD
    protected const DIFFICULTY_MAP = [
        'Básico' => 1,
        'Intermedio' => 2,
        'Avanzado' => 3,
    ];

    // Mapeo inverso: valor de BD a texto (para mostrar en vistas)
    protected const DIFFICULTY_REVERSE_MAP = [
        1 => 'Básico',
        2 => 'Intermedio',
        3 => 'Avanzado',
    ];

    public function __construct()
    {
        $this->contenidoModel = new ContenidoModel();
    }

    /**
     * Muestra la lista de contenidos con filtros y paginación.
     */
    public function index(Request $request)
    {
        // ✅ Obtener contenidos filtrados desde PDO
        $filters = [
            'subject_area' => $request->get('subject_area'),
            'type' => $request->get('type'),
            'difficulty_level' => $request->get('difficulty_level'),
            'search' => $request->get('search'),
        ];

        $contents = $this->contenidoModel->listarContenidosFiltrados($filters);

        if (is_array($contents) || ($contents instanceof \Illuminate\Support\Collection)) {
            $contents = collect($contents)->map(function($item) {
                return (object) $item;
            });
        }

        // ✅ Obtener datos únicos para los filtros
        $subjects = $this->contenidoModel->obtenerAreasUnicas();
        $types = $this->contenidoModel->obtenerTiposUnicos();

        return view('admin.content.index', compact('contents', 'subjects', 'types'));
    }

    /**
     * Muestra el formulario para crear un nuevo contenido.
     */
    public function create()
    {
        // Datos estáticos para los selectores del formulario
        $subjects = ['Matemáticas', 'Física', 'Química', 'Programación', 'Historia', 'Biología'];
        $types = ['Video', 'Documento', 'Interactivo', 'Quiz', 'Artículo'];
        $difficulties = array_keys(self::DIFFICULTY_MAP); // ['Básico', 'Intermedio', 'Avanzado']
        
        return view('admin.content.create', compact('subjects', 'types', 'difficulties'));
    }

    /**
     * Almacena un nuevo contenido en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_area' => 'required|string|max:100',
            'topic' => 'nullable|string|max:100',
            'type' => 'required|in:Video,Documento,Interactivo,Quiz,Artículo',
            'difficulty_level' => 'required|in:' . implode(',', array_keys(self::DIFFICULTY_MAP)),
            'description' => 'nullable|string',
            'content_url' => 'nullable|url|max:500',
            'duration_minutes' => 'nullable|integer|min:1',
            'tags' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);

        // Asegurar que active sea boolean
        $validated['active'] = $request->has('active') ? 1 : 0;

        // 🚨 CRÍTICO: Convertir dificultad de texto a ENUM de BD
        // La BD usa ENUM('Básico', 'Intermedio', 'Avanzado'), no enteros
        // Mantener el texto original
        $validated['difficulty_level'] = $validated['difficulty_level'];

        // ✅ Crear contenido usando PDO
        $contentId = $this->contenidoModel->crearContenido($validated);
        
        if (!$contentId) {
            return redirect()->back()
                ->with('error', 'Error al crear el contenido.')
                ->withInput();
        }

        return redirect()->route('admin.content.index')
            ->with('success', 'Contenido creado exitosamente.');
    }

    /**
     * Muestra un contenido específico.
     */
    public function show($id)
    {
        // ✅ Buscar contenido usando PDO
        $content = $this->contenidoModel->obtenerContenido($id);
        
        if (!$content) {
            abort(404, 'Contenido no encontrado.');
        }
        $content = (object) $content;

        return view('admin.content.show', compact('content'));
    }

    /**
     * Muestra el formulario para editar un contenido.
     */
    public function edit($id)
    {
        // ✅ Buscar contenido usando PDO
        $content = $this->contenidoModel->obtenerContenido($id);
        
        if (!$content) {
            abort(404, 'Contenido no encontrado.');
        }
        $content = (object) $content;

        // Datos estáticos para los selectores del formulario
        $subjects = ['Matemáticas', 'Física', 'Química', 'Programación', 'Historia', 'Biología'];
        $types = ['Video', 'Documento', 'Interactivo', 'Quiz', 'Artículo'];
        $difficulties = array_keys(self::DIFFICULTY_MAP);
        
        return view('admin.content.edit', compact('content', 'subjects', 'types', 'difficulties'));
    }

    /**
     * Actualiza un contenido existente.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_area' => 'required|string|max:100',
            'topic' => 'nullable|string|max:100',
            'type' => 'required|in:Video,Documento,Interactivo,Quiz,Artículo',
            'difficulty_level' => 'required|in:' . implode(',', array_keys(self::DIFFICULTY_MAP)),
            'description' => 'nullable|string',
            'content_url' => 'nullable|url|max:500',
            'duration_minutes' => 'nullable|integer|min:1',
            'tags' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);

        $validated['active'] = $request->has('active') ? 1 : 0;

        // Mantener el texto de dificultad (ENUM en BD)
        $validated['difficulty_level'] = $validated['difficulty_level'];

        // ✅ Actualizar contenido usando PDO
        $updated = $this->contenidoModel->actualizarContenido($id, $validated);
        
        if (!$updated) {
            return redirect()->back()
                ->with('error', 'Error al actualizar el contenido.')
                ->withInput();
        }

        return redirect()->route('admin.content.index')
            ->with('success', 'Contenido actualizado exitosamente.');
    }

    /**
     * Elimina un contenido específico.
     */
    public function destroy($id)
    {
        // ✅ Eliminar contenido usando PDO
        $deleted = $this->contenidoModel->eliminarContenido($id);
        
        if (!$deleted) {
            return redirect()->back()
                ->with('error', 'Error al eliminar el contenido.');
        }

        return redirect()->route('admin.content.index')
            ->with('success', 'Contenido eliminado exitosamente.');
    }

    /**
     * Maneja la carga masiva de contenidos desde CSV/Excel.
     */
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls',
        ]);

        // TODO: Implementar procesamiento de archivo
        // $result = $this->contenidoModel->cargaMasivaContenidos($request->file('file'));
        
        return redirect()->route('admin.content.index')
            ->with('info', 'Carga masiva pendiente de implementar.');
    }

    /**
     * Incrementa el contador de vistas de un contenido.
     */
    public function incrementViews($id)
    {
        $this->contenidoModel->incrementarVistas($id);
        
        return response()->json(['success' => true]);
    }
}
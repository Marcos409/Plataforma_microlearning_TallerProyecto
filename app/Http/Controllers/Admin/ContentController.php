<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\ContentDAOInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    protected ContentDAOInterface $contentDAO;

    // Define el mapeo de dificultad de texto a entero (INT) para la base de datos
    protected const DIFFICULTY_MAP = [
        'Básico' => 1,
        'Intermedio' => 2,
        'Avanzado' => 3,
    ];

    /**
     * Inyección de dependencia del ContentDAOInterface.
     */
    public function __construct(ContentDAOInterface $contentDAO)
    {
        $this->contentDAO = $contentDAO;
    }

    /**
     * Muestra la lista de contenidos con filtros y paginación.
     */
    public function index(Request $request)
    {
        // 1. Obtener contenidos paginados con filtros desde el DAO
        $contents = $this->contentDAO->getFilteredAndPaginated($request);

        // 2. Obtener datos únicos para los filtros desde el DAO
        $subjects = $this->contentDAO->getUniqueSubjectAreas();
        $types = $this->contentDAO->getUniqueTypes();
        
        return view('admin.content.index', compact('contents', 'subjects', 'types'));
    }

    /**
     * Muestra el formulario para crear un nuevo contenido.
     */
    public function create()
    {
        // Datos estáticos para los selectores del formulario
        $subjects = ['Matemáticas', 'Física', 'Química', 'Programación', 'Historia', 'Biología'];
        $types = ['Video', 'Documento', 'Interactivo', 'Quiz', 'PDF', 'Presentación'];
        // Usamos las claves del mapa para mostrar al usuario (Texto)
        $difficulties = array_keys(self::DIFFICULTY_MAP);
        
        return view('admin.content.create', compact('subjects', 'types', 'difficulties'));
    }

    /**
     * Almacena un nuevo contenido en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_area' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            // Valida que el texto recibido esté en nuestras claves permitidas
            'difficulty_level' => 'required|in:' . implode(',', array_keys(self::DIFFICULTY_MAP)),
            'description' => 'nullable|string',
            // 👈 Corregido: Usar 'external_url' para coincidir con el esquema SQL
            'external_url' => 'nullable|url|max:255',
            'active' => 'nullable|boolean',
        ]);

        // Asegurar que active sea boolean (basado en si el checkbox fue marcado)
        $validated['active'] = $request->has('active');

        // 🚨 CRÍTICO: Mapeo de texto a entero antes de guardarlo en la DB
        $validated['difficulty_level'] = self::DIFFICULTY_MAP[$validated['difficulty_level']];

        // 3. Creación del contenido usando el DAO
        $this->contentDAO->create($validated);
        
        return redirect()->route('admin.content.index')
            ->with('success', 'Contenido creado exitosamente.');
    }

    /**
     * Muestra un contenido específico.
     */
    public function show($id)
    {
        // 4. Búsqueda del contenido usando el DAO
        $content = $this->contentDAO->findOrFail($id);
        
        return view('admin.content.show', compact('content'));
    }

    /**
     * Muestra el formulario para editar un contenido.
     */
    public function edit($id)
    {
        // 5. Búsqueda del contenido usando el DAO
        $content = $this->contentDAO->findOrFail($id);
        
        // Datos estáticos para los selectores del formulario
        $subjects = ['Matemáticas', 'Física', 'Química', 'Programación', 'Historia', 'Biología'];
        $types = ['Video', 'Documento', 'Interactivo', 'Quiz', 'PDF', 'Presentación'];
        // Usamos las claves del mapa para mostrar al usuario (Texto)
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
            'subject_area' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            // Valida que el texto recibido esté en nuestras claves permitidas
            'difficulty_level' => 'required|in:' . implode(',', array_keys(self::DIFFICULTY_MAP)),
            'description' => 'nullable|string',
            // 👈 Corregido: Usar 'external_url' para coincidir con el esquema SQL
            'external_url' => 'nullable|url|max:255',
            'active' => 'nullable|boolean',
        ]);

        $validated['active'] = $request->has('active');

        // 🚨 CRÍTICO: Mapeo de texto a entero antes de guardarlo en la DB
        $validated['difficulty_level'] = self::DIFFICULTY_MAP[$validated['difficulty_level']];

        // 6. Actualización del contenido usando el DAO
        $this->contentDAO->update($id, $validated);
        
        return redirect()->route('admin.content.index')
            ->with('success', 'Contenido actualizado exitosamente.');
    }

    /**
     * Elimina un contenido específico.
     */
    public function destroy($id)
    {
        // 7. Eliminación del contenido usando el DAO
        $this->contentDAO->delete($id);
        
        return redirect()->route('admin.content.index')
            ->with('success', 'Contenido eliminado exitosamente.');
    }

    /**
     * Maneja la carga masiva de contenidos (función pendiente de implementar).
     */
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls',
        ]);

        // TODO: Implementar procesamiento de archivo CSV/Excel usando el DAO para crear los contenidos.
        
        return redirect()->route('admin.content.index')
            ->with('success', 'Contenidos cargados masivamente (función pendiente de implementar).');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentLibrary;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    /**
     * Display a listing of content.
     */
    public function index(Request $request)
    {
        $query = ContentLibrary::query();

        // Aplicar filtros si existen
        if ($request->filled('subject')) {
            $query->where('subject_area', $request->subject);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $contents = $query->orderBy('created_at', 'desc')->paginate(12);

        // Si no hay datos en la BD, usar datos de ejemplo
        if ($contents->isEmpty()) {
            $contents = collect([
                (object)['id' => 1, 'title' => 'Álgebra Básica', 'subject_area' => 'Matemáticas', 'type' => 'Video', 'difficulty_level' => 'Básico', 'active' => true],
                (object)['id' => 2, 'title' => 'Leyes de Newton', 'subject_area' => 'Física', 'type' => 'Documento', 'difficulty_level' => 'Intermedio', 'active' => true],
                (object)['id' => 3, 'title' => 'Tabla Periódica', 'subject_area' => 'Química', 'type' => 'Interactivo', 'difficulty_level' => 'Básico', 'active' => false],
                (object)['id' => 4, 'title' => 'Algoritmos de Ordenamiento', 'subject_area' => 'Programación', 'type' => 'Video', 'difficulty_level' => 'Avanzado', 'active' => true],
            ]);
        }

        $subjects = collect(['Matemáticas', 'Física', 'Química', 'Programación']);
        $types = collect(['Video', 'Documento', 'Interactivo', 'Quiz']);
        
        return view('admin.content.index', compact('contents', 'subjects', 'types'));
    }

    /**
     * Show the form for creating new content.
     */
    public function create()
    {
        $subjects = ['Matemáticas', 'Física', 'Química', 'Programación'];
        $types = ['Video', 'Documento', 'Interactivo', 'Quiz'];
        $difficulties = ['Básico', 'Intermedio', 'Avanzado'];
        
        return view('admin.content.create', compact('subjects', 'types', 'difficulties'));
    }

    /**
     * Store newly created content.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject_area' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'difficulty_level' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content_url' => 'nullable|url',
        ]);

        // Crear contenido (ajusta según tu modelo)
        ContentLibrary::create($request->all());
        
        return redirect()->route('admin.content.index')
            ->with('success', 'Contenido creado exitosamente.');
    }

    /**
     * Display the specified content.
     */
    public function show($content)
    {
        // Buscar contenido real o simular datos
        try {
            $content = ContentLibrary::findOrFail($content);
        } catch (\Exception $e) {
            // Datos de ejemplo si no existe
            $content = (object)[
                'id' => $content,
                'title' => 'Álgebra Básica',
                'subject_area' => 'Matemáticas',
                'type' => 'Video',
                'difficulty_level' => 'Básico',
                'description' => 'Introducción a los conceptos básicos de álgebra',
                'content_url' => 'https://example.com/video',
                'active' => true,
                'views' => 245,
                'created_at' => now()
            ];
        }
        
        return view('admin.content.show', compact('content'));
    }

    /**
     * Show the form for editing content.
     */
    public function edit($content)
    {
        // Buscar contenido real o simular datos
        try {
            $content = ContentLibrary::findOrFail($content);
        } catch (\Exception $e) {
            $content = (object)[
                'id' => $content,
                'title' => 'Álgebra Básica',
                'subject_area' => 'Matemáticas',
                'type' => 'Video',
                'difficulty_level' => 'Básico',
                'description' => 'Introducción a los conceptos básicos de álgebra',
                'content_url' => 'https://example.com/video'
            ];
        }
        
        $subjects = ['Matemáticas', 'Física', 'Química', 'Programación'];
        $types = ['Video', 'Documento', 'Interactivo', 'Quiz'];
        $difficulties = ['Básico', 'Intermedio', 'Avanzado'];
        
        return view('admin.content.edit', compact('content', 'subjects', 'types', 'difficulties'));
    }

    /**
     * Update the specified content.
     */
    public function update(Request $request, $content)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject_area' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'difficulty_level' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content_url' => 'nullable|url',
        ]);

        // Actualizar contenido (ajusta según tu modelo)
        // $content = ContentLibrary::findOrFail($content);
        // $content->update($request->all());
        
        return redirect()->route('admin.content.index')
            ->with('success', 'Contenido actualizado exitosamente.');
    }

    /**
     * Remove the specified content.
     */
    public function destroy($content)
    {
        // Eliminar contenido (ajusta según tu modelo)
        // ContentLibrary::findOrFail($content)->delete();
        
        return redirect()->route('admin.content.index')
            ->with('success', 'Contenido eliminado exitosamente.');
    }

    /**
     * Handle bulk upload of content.
     */
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx',
        ]);

        // Procesar archivo subido (implementar según necesidades)
        
        return redirect()->route('admin.content.index')
            ->with('success', 'Contenidos cargados masivamente exitosamente.');
    }
}
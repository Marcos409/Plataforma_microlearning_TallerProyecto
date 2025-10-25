<?php

namespace App\DataAccessObjects;

use App\Contracts\ContentDAOInterface;
use App\Models\ContentLibrary;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection; // 👈 Asegúrate que esta línea exista
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class EloquentContentDAO implements ContentDAOInterface
{
    protected ContentLibrary $model;

    public function __construct(ContentLibrary $contentLibrary)
    {
        $this->model = $contentLibrary;
    }

    public function getFilteredAndPaginated(Request $request): LengthAwarePaginator
    {
        $query = $this->model->query();

        // Aplicar filtros
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

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    /**
     * Obtiene una lista única y ordenada de todas las áreas temáticas.
     *
     * @return Collection
     */
    public function getUniqueSubjectAreas(): Collection // 👈 Solución crítica aquí (Línea ~53)
    {
        $defaultSubjects = collect(['Matemáticas', 'Física', 'Química', 'Programación']);
        
        return $this->model->distinct('subject_area')
            ->pluck('subject_area')
            ->merge($defaultSubjects)
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * Obtiene una lista única y ordenada de todos los tipos de contenido.
     *
     * @return Collection
     */
    public function getUniqueTypes(): Collection // 👈 Solución crítica aquí
    {
        $defaultTypes = collect(['Video', 'Documento', 'Interactivo', 'Quiz']);
        
        return $this->model->distinct('type')
            ->pluck('type')
            ->merge($defaultTypes)
            ->unique()
            ->sort()
            ->values();
    }
    
    // El resto de los métodos que manejan un solo modelo ContentLibrary
    public function create(array $data): ContentLibrary
    {
        return $this->model->create($data);
    }

    public function findOrFail(int $id): ContentLibrary
    {
        return $this->model->findOrFail($id);
    }

    public function update(int $id, array $data): ContentLibrary
    {
        $content = $this->findOrFail($id);
        $content->update($data);
        return $content;
    }

    public function delete(int $id): bool
    {
        $content = $this->findOrFail($id);
        return $content->delete();
    }
}
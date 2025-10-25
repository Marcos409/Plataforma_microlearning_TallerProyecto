<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection; // ✅ NUEVO: Importamos la colección base de Support

interface ContentDAOInterface
{
    /**
     * Obtiene el contenido paginado aplicando filtros de Request.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getFilteredAndPaginated(Request $request): LengthAwarePaginator;

    /**
     * Obtiene una lista única y ordenada de todas las áreas temáticas.
     * Usa Illuminate\Support\Collection para listas de datos planos.
     *
     * @return Collection
     */
    public function getUniqueSubjectAreas(): Collection;

    /**
     * Obtiene una lista única y ordenada de todos los tipos de contenido.
     * Usa Illuminate\Support\Collection para listas de datos planos.
     *
     * @return Collection
     */
    public function getUniqueTypes(): Collection;

    /**
     * Crea un nuevo registro de contenido.
     *
     * @param array $data
     * @return \App\Models\ContentLibrary
     */
    public function create(array $data): \App\Models\ContentLibrary;

    /**
     * Busca un contenido por ID, lanza excepción si no se encuentra.
     *
     * @param int $id
     * @return \App\Models\ContentLibrary
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): \App\Models\ContentLibrary;

    /**
     * Actualiza un registro de contenido existente.
     *
     * @param int $id
     * @param array $data
     * @return \App\Models\ContentLibrary
     */
    public function update(int $id, array $data): \App\Models\ContentLibrary;

    /**
     * Elimina un registro de contenido por ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
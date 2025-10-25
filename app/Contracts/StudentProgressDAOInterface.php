<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface StudentProgressDAOInterface
{
    /**
     * Obtener todo el progreso
     */
    public function getAll(): Collection;

    /**
     * Obtener progreso por usuario
     */
    public function getByUser(int $userId): Collection;

    /**
     * Crear registro de progreso
     */
    public function create(array $data): ?int;

    /**
     * Actualizar progreso
     */
    public function update(int $progressId, array $data): bool;
}
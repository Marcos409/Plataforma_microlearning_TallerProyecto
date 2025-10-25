<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface FollowUpDAOInterface
{
    /**
     * Crear nuevo seguimiento
     */
    public function create(array $data): ?int;

    /**
     * Obtener seguimiento por ID
     */
    public function findById(int $id): ?object;

    /**
     * Obtener todos los seguimientos
     */
    public function getAll(): Collection;

    /**
     * Obtener seguimientos pendientes
     */
    public function getPending(): Collection;

    /**
     * Obtener seguimientos vencidos
     */
    public function getOverdue(): Collection;

    /**
     * Obtener seguimientos por usuario
     */
    public function getByUser(int $userId): Collection;

    /**
     * Obtener seguimientos por admin
     */
    public function getByAdmin(int $adminId): Collection;

    /**
     * Marcar como completado
     */
    public function markAsCompleted(int $followUpId): bool;

    /**
     * Actualizar seguimiento
     */
    public function update(int $followUpId, array $data): bool;

    /**
     * Eliminar seguimiento
     */
    public function delete(int $followUpId): bool;

    /**
     * Obtener estadísticas
     */
    public function getStats(): array;
}
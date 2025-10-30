<?php

namespace App\Contracts;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserDAOInterface
{
    /**
     * Obtener todos los usuarios
     */
    public function getAll(): Collection;

    /**
     * Obtener usuario por ID
     */
    public function findById(int $id): ?object;

    /**
     * Obtener usuarios por rol
     */
    public function getUsersByRole(int $roleId): Collection;

    /**
     * Obtener usuarios activos
     */
    public function getActiveUsers(): Collection;

    /**
     * Crear nuevo usuario
     */
    public function create(array $data): ?int;

    /**
     * Actualizar usuario
     */
    public function update(int $userId, array $data): bool;

    /**
     * Eliminar usuario
     */
    public function delete(int $userId): bool;

    /**
     * Obtener estudiantes
     */
    public function getStudents(): Collection;

    /**
     * Obtener docentes
     */
    public function getTeachers(): Collection;

    /**
     * Obtener administradores
     */
    public function getAdmins(): Collection;

    /**
     * Obtener progreso general de un estudiante
     */
    public function getOverallProgress(int $userId): float;

    /**
     * Obtener tiempo total gastado
     */
    public function getTotalTimeSpent(int $userId): int;

    /**
     * Obtener actividades completadas
     */
    public function getCompletedActivitiesCount(int $userId): int;

    /**
     * Verificar si tiene alertas activas
     */
    public function hasActiveRiskAlerts(int $userId): bool;

    /**
     * Obtener alertas activas
     */
    public function getActiveRiskAlerts(int $userId): Collection;

    /**
     * Obtener recomendaciones pendientes
     */
    public function getPendingRecommendationsCount(int $userId): int;

    /**
     * Actualizar última actividad
     */
    public function updateLastActivity(int $userId): bool;

    /**
     * Verificar inactividad
     */
    public function isInactiveFor(int $userId, int $days): bool;

    /**
     * Contar usuarios por rol
     */
    public function countByRole(int $roleId): int;

    /**
     * Buscar usuarios
     */
    public function search(?string $search, ?int $roleId, ?bool $active): Collection;

    /**
     * Obtener estudiantes con filtros
     */
    public function getStudentsWithFilters(?string $search, ?string $status): Collection;

    /**
     * Obtener usuarios con filtros para administración (con paginación)
     */
    public function getUsersWithFilters(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    /**
     * Obtener usuarios sin rol asignado (con paginación)
     */
    public function getUsersPendingRole(): LengthAwarePaginator;
    
    /**
     * Actualizar roles de múltiples usuarios
     */
    public function bulkUpdateRoles(array $userIds, int $roleId): int;
    
    /**
     * Obtener usuarios para exportar (sin paginación)
     */
    public function getUsersForExport(array $filters = []): Collection;
    
    /**
     * Obtener estudiantes por carrera
     */
    public function getStudentsByCareer(string $career): Collection;
    
    /**
     * Obtener cantidad de usuarios pendientes
     */
    public function getPendingUsersCount(): int;
}
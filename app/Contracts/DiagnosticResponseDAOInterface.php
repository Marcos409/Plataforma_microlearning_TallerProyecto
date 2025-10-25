<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface DiagnosticResponseDAOInterface
{
    /**
     * Obtener rendimiento por materia
     */
    public function getPerformanceBySubject(): Collection;

    /**
     * Obtener rendimiento de un estudiante
     */
    public function getStudentPerformance(int $userId): array;

    /**
     * Contar diagnósticos completados por usuario
     */
    public function countCompletedDiagnostics(int $userId): int;

    /**
     * Crear nueva respuesta
     */
    public function create(array $data): ?int;

    /**
     * Obtener respuestas por usuario
     */
    public function getByUser(int $userId): Collection;

    /**
     * Obtener respuestas por diagnóstico
     */
    public function getByDiagnostic(int $diagnosticId): Collection;

    /**
     * Obtener respuestas incorrectas
     */
    public function getIncorrectResponses(int $userId): Collection;

    /**
     * Obtener todas las respuestas
     */
    public function getAll(): Collection;

    /**
     * Obtener respuesta por ID
     */
    public function findById(int $id): ?object;
}
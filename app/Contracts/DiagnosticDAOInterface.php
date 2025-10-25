<?php

namespace App\Contracts;

use App\Models\Diagnostic;
use App\Models\DiagnosticQuestion;
use Illuminate\Support\Collection;

/**
 * Define el contrato para la capa de acceso a datos de Diagnósticos y Preguntas.
 * Todos los métodos del controlador deben depender de esta interfaz, no de la implementación.
 */
interface DiagnosticDAOInterface
{
    /** Diagnósticos (Padre) **/

    /**
     * Obtiene todos los diagnósticos, incluyendo el conteo de preguntas, ordenados por creación.
     * @return Collection<Diagnostic>
     */
    public function getAllWithQuestionCount(): Collection;

    /**
     * Busca un diagnóstico por ID o falla, incluyendo sus preguntas.
     * @param int $id
     * @return Diagnostic
     */
    public function findWithQuestions(int $id): Diagnostic;

    /**
     * Crea un nuevo diagnóstico.
     * @param array $data
     * @return Diagnostic
     */
    public function createDiagnostic(array $data): Diagnostic;

    /**
     * Actualiza un diagnóstico existente.
     * @param Diagnostic $diagnostic
     * @param array $data
     * @return Diagnostic
     */
    public function updateDiagnostic(Diagnostic $diagnostic, array $data): Diagnostic;

    /**
     * Elimina un diagnóstico.
     * @param Diagnostic $diagnostic
     */
    public function deleteDiagnostic(Diagnostic $diagnostic): void;


    /** Preguntas (Hijo) **/

    /**
     * Obtiene las preguntas de un diagnóstico específico, ordenadas.
     * @param Diagnostic $diagnostic
     * @return Collection<DiagnosticQuestion>
     */
    public function getQuestionsForDiagnostic(Diagnostic $diagnostic): Collection;

    /**
     * Crea una nueva pregunta para un diagnóstico.
     * @param Diagnostic $diagnostic
     * @param array $data
     * @return DiagnosticQuestion
     */
    public function createQuestion(Diagnostic $diagnostic, array $data): DiagnosticQuestion;

    /**
     * Actualiza una pregunta existente.
     * @param DiagnosticQuestion $question
     * @param array $data
     * @return DiagnosticQuestion
     */
    public function updateQuestion(DiagnosticQuestion $question, array $data): DiagnosticQuestion;

    /**
     * Elimina una pregunta.
     * @param DiagnosticQuestion $question
     */
    public function deleteQuestion(DiagnosticQuestion $question): void;

    /**
     * Actualiza el conteo total de preguntas para un diagnóstico.
     * @param Diagnostic $diagnostic
     */
    public function syncTotalQuestions(Diagnostic $diagnostic): void;
}

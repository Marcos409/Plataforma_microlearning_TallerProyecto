<?php

namespace App\DataAccessObjects;

use App\Contracts\DiagnosticDAOInterface;
use App\Models\Diagnostic;
use App\Models\DiagnosticQuestion;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EloquentDiagnosticDAO implements DiagnosticDAOInterface
{
    /** Diagnósticos (Padre) **/

    public function getAllWithQuestionCount(): Collection
    {
        return Diagnostic::withCount('questions')->orderBy('created_at', 'desc')->get();
    }

    public function findWithQuestions(int $id): Diagnostic
    {
        $diagnostic = Diagnostic::with('questions')->find($id);

        if (!$diagnostic) {
            throw new ModelNotFoundException("Diagnostic with ID {$id} not found.");
        }
        return $diagnostic;
    }

    public function createDiagnostic(array $data): Diagnostic
    {
        return Diagnostic::create($data);
    }

    public function updateDiagnostic(Diagnostic $diagnostic, array $data): Diagnostic
    {
        $diagnostic->update($data);
        return $diagnostic;
    }

    public function deleteDiagnostic(Diagnostic $diagnostic): void
    {
        // Eloquent gestiona la eliminación de preguntas si se configura correctamente
        // (usando foreign keys ON DELETE CASCADE), pero eliminamos manualmente para mayor seguridad.
        $diagnostic->questions()->delete();
        $diagnostic->delete();
    }


    /** Preguntas (Hijo) **/

    public function getQuestionsForDiagnostic(Diagnostic $diagnostic): Collection
    {
        return $diagnostic->questions()->orderBy('created_at')->get();
    }

    public function createQuestion(Diagnostic $diagnostic, array $data): DiagnosticQuestion
    {
        // Se asegura de que la pregunta se cree directamente asociada al diagnóstico
        return $diagnostic->questions()->create($data);
    }

    public function updateQuestion(DiagnosticQuestion $question, array $data): DiagnosticQuestion
    {
        $question->update($data);
        return $question;
    }

    public function deleteQuestion(DiagnosticQuestion $question): void
    {
        $question->delete();
    }

    public function syncTotalQuestions(Diagnostic $diagnostic): void
    {
        // Utilizamos el método ya existente en el modelo, pero encapsulado
        $diagnostic->updateTotalQuestions();
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\AIService;

class PredictDifficulties extends Command
{
    protected $signature = 'ai:predict-difficulties';
    protected $description = 'Ejecuta la predicción de dificultades para todos los estudiantes activos';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(AIService $aiService)
    {
        $students = User::where('role_id', 3) // Estudiantes
            ->where('active', true)
            ->where('last_activity', '>=', now()->subDays(30))
            ->get();

        $this->info("Iniciando predicción de dificultades para {$students->count()} estudiantes...");

        $bar = $this->output->createProgressBar($students->count());

        foreach ($students as $student) {
            $aiService->predictDifficulties($student);
            $bar->advance();
        }

        $bar->finish();
        $this->info("\n¡Predicción de dificultades completada!");
    }
}
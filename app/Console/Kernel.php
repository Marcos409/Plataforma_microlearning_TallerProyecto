<?php

protected function schedule(Schedule $schedule)
{
    // Predicción de dificultades diaria
    $schedule->command('ai:predict-difficulties')
             ->dailyAt('06:00')
             ->emailOutputOnFailure('admin@continental.edu.pe');

    // Reportes semanales cada lunes
    $schedule->command('reports:weekly')
             ->weeklyOn(1, '08:00')
             ->emailOutputOnFailure('admin@continental.edu.pe');

    // Limpieza de datos antiguos cada mes
    $schedule->call(function () {
        // Eliminar alertas resueltas de más de 6 meses
        RiskAlert::where('is_resolved', true)
                 ->where('resolved_at', '<', now()->subMonths(6))
                 ->delete();
    })->monthlyOn(1, '02:00');
}
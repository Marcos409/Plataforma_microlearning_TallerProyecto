<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RiskAlert;
use App\Models\Report;
use Illuminate\Support\Facades\Mail;

class GenerateWeeklyReports extends Command
{
    protected $signature = 'reports:weekly';
    protected $description = 'Genera reportes semanales automáticos';

    public function handle()
    {
        $this->info('Generando reportes semanales...');

        // Obtener estudiantes en riesgo
        $studentsAtRisk = User::whereHas('riskAlerts', function($query) {
            $query->where('is_resolved', false)
                  ->whereIn('severity', ['high', 'critical']);
        })->with(['riskAlerts' => function($query) {
            $query->where('is_resolved', false);
        }])->get();

        // Crear reporte
        $reportData = [
            'period' => now()->subWeek()->format('Y-m-d') . ' a ' . now()->format('Y-m-d'),
            'students_at_risk' => $studentsAtRisk->count(),
            'total_alerts' => RiskAlert::where('created_at', '>=', now()->subWeek())->count(),
            'students_data' => $studentsAtRisk->map(function($student) {
                return [
                    'name' => $student->name,
                    'email' => $student->email,
                    'student_code' => $student->student_code,
                    'alerts' => $student->riskAlerts->map(function($alert) {
                        return [
                            'type' => $alert->type,
                            'title' => $alert->title,
                            'severity' => $alert->severity,
                            'created_at' => $alert->created_at->format('Y-m-d H:i')
                        ];
                    })
                ];
            })
        ];

        // Guardar reporte
        $report = Report::create([
            'name' => 'Reporte Semanal de Riesgo - ' . now()->format('Y-m-d'),
            'type' => 'general',
            'data' => $reportData,
            'generated_by' => 1 // Sistema
        ]);

        // Enviar por email a administradores y docentes
        $recipients = User::whereIn('role_id', [1, 2])->get();

        foreach ($recipients as $recipient) {
            // Aquí iría la lógica de envío de email
            // Mail::to($recipient->email)->send(new WeeklyReportMail($report));
        }

        $this->info("Reporte semanal generado: {$report->name}");
        $this->info("Estudiantes en riesgo: {$studentsAtRisk->count()}");
    }
}
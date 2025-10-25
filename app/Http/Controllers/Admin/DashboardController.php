<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\UserDAOInterface;
use App\Contracts\FollowUpDAOInterface;
use App\Contracts\DiagnosticResponseDAOInterface;
use App\Models\StudentProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB; 

class DashboardController extends Controller
{
    protected UserDAOInterface $userDAO;
    protected FollowUpDAOInterface $followUpDAO;
    protected DiagnosticResponseDAOInterface $diagnosticResponseDAO;

    /**
     * Inyección de dependencias
     */
    public function __construct(
        UserDAOInterface $userDAO,
        FollowUpDAOInterface $followUpDAO,
        DiagnosticResponseDAOInterface $diagnosticResponseDAO
    ) {
        $this->userDAO = $userDAO;
        $this->followUpDAO = $followUpDAO;
        $this->diagnosticResponseDAO = $diagnosticResponseDAO;
    }

    public function index()
    {
        $riskAlerts = collect([]);
        $mlAlerts = null;
        $mlAnalysis = null;
        $mlRecommendations = null;
        $overallProgress = 0;
        $learningPaths = collect([]);
        $recommendations = collect([]);
        $subjectProgress = collect([]);
        $recentActivity = collect([]);
        
        // ✅ USA DAO si existe el método getPendingUsersCount
        // Si no existe, comenta esta línea
        // $pendingUsersCount = $this->userDAO->getPendingUsersCount();
        
        return view('admin.dashboard', compact(
            'riskAlerts',
            'mlAlerts',
            'mlAnalysis',
            'mlRecommendations',
            'overallProgress',
            'learningPaths',
            'recommendations',
            'subjectProgress',
            'recentActivity'
            // 'pendingUsersCount'
        ));
    }

    public function reports()
    {
        // ✅ CAMBIADO: USA DAO en lugar de User::students()
        $totalStudents = $this->userDAO->countByRole(3); // 3 = role_id estudiante
        $activeTeachers = $this->userDAO->countByRole(2); // 2 = role_id docente
        
        // Mantener si no hay procedure específico
        $completedActivities = StudentProgress::sum('completed_activities');
        
        $averageSatisfaction = 89;
        
        return view('admin.reports.index', compact(
            'totalStudents',
            'activeTeachers', 
            'completedActivities',
            'averageSatisfaction'
        ));
    }

    public function studentReports(Request $request)
    {
        // ✅ CAMBIADO: USA DAO con filtros
        $studentsRaw = $this->userDAO->getStudentsWithFilters(
            $request->search,
            $request->status
        );
        
        // Convertir a paginación
        $students = new \Illuminate\Pagination\LengthAwarePaginator(
            $studentsRaw->take(20),
            $studentsRaw->count(),
            20,
            $request->input('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // ✅ CAMBIADO: Calcular métricas usando DAO
        foreach ($students as $student) {
            // Usar DAO en lugar de relaciones del modelo
            $student = (object) $student; // Ensure $student is an object
            $student->total_diagnostics = $this->diagnosticResponseDAO->countCompletedDiagnostics($student->id);
            $student->completed_activities = $this->userDAO->getCompletedActivitiesCount($student->id);
            
            // Obtener rendimiento via DAO
            $performance = $this->diagnosticResponseDAO->getStudentPerformance($student->id);
            $student->average_score = $performance['percentage'];
            
            $student->risk_level = $this->calculateRiskLevel($student);
        }
        
        return view('admin.reports.students', compact('students'));
    }

    public function performanceReports(Request $request)
    {
        // ✅ CAMBIADO: USA DAO
        $performanceBySubject = $this->diagnosticResponseDAO->getPerformanceBySubject();
        
        // ✅ CAMBIADO: Top estudiantes usando DAO
        $allStudents = $this->userDAO->getStudents();
        $topStudents = $allStudents->map(function($student) {
            $performance = $this->diagnosticResponseDAO->getStudentPerformance($student->id);
            
            $student->total_responses = $performance['total_responses'];
            $student->correct_responses = $performance['correct_responses'];
            $student->average_score = $performance['percentage'];
            
            return $student;
        })
        ->filter(function($student) {
            return $student->total_responses > 0;
        })
        ->sortByDesc('average_score')
        ->take(10);
        
        // Rendimiento por período - mantener si no hay procedure
        $performanceByMonth = \App\Models\DiagnosticResponse::select(
                DB::raw('MONTH(diagnostic_responses.created_at) as month'),
                DB::raw('YEAR(diagnostic_responses.created_at) as year'),
                DB::raw('COUNT(*) as total_attempts'),
                DB::raw('SUM(CASE WHEN diagnostic_responses.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers'),
                DB::raw('ROUND((SUM(CASE WHEN diagnostic_responses.is_correct = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as avg_score')
            )
            ->where('diagnostic_responses.created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
        
        return view('admin.reports.performance', compact(
            'performanceBySubject',
            'topStudents',
            'performanceByMonth'
        ));
    }

    public function riskReports()
    {
        // ✅ CAMBIADO: USA DAO
        $students = $this->userDAO->getStudents();
        
        $atRiskStudents = $students->filter(function($student) {
            return $this->calculateRiskLevel($student) >= 1;
        })->sortByDesc(function($student) {
            return $this->calculateRiskLevel($student);
        });
        
        foreach ($atRiskStudents as $student) {
            $student->risk_level = $this->calculateRiskLevel($student);
            $student->risk_factors = $this->identifyRiskFactors($student);
        }
        
        return view('admin.reports.risk', compact('atRiskStudents'));
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'report_type' => 'required|string',
            'period' => 'required|string',
            'format' => 'required|in:pdf,excel,csv',
        ]);
        
        return redirect()->route('admin.reports.index')
            ->with('success', 'Reporte generado exitosamente. (Función en desarrollo)');
    }

    public function systemMonitoring()
    {
        return view('admin.monitoring.index');
    }

    public function usageStats()
    {
        return view('admin.monitoring.usage');
    }

    // ==================== MÉTODOS AUXILIARES ====================

    /**
     * ✅ CAMBIADO: Calcula riesgo usando DAO
     */
    private function calculateRiskLevel($student)
    {
        $riskScore = 0;
        
        // Factor 1: Inactividad
        $lastActivity = $student->last_activity ?? null;
        
        if (!$lastActivity) {
            $riskScore += 2;
        } elseif (strtotime($lastActivity) < strtotime('-14 days')) {
            $riskScore += 2;
        } elseif (strtotime($lastActivity) < strtotime('-7 days')) {
            $riskScore += 1;
        }
        
        // Factor 2: Bajo rendimiento - usar DAO
        $performance = $this->diagnosticResponseDAO->getStudentPerformance($student->id);
        $avgScore = $performance['percentage'];
        
        if ($avgScore > 0 && $avgScore < 50) {
            $riskScore += 2;
        } elseif ($avgScore > 0 && $avgScore < 70) {
            $riskScore += 1;
        }
        
        // Factor 3: Baja participación - usar DAO
        $totalActivities = $this->diagnosticResponseDAO->countCompletedDiagnostics($student->id);
        
        $createdAt = strtotime($student->created_at);
        $fourteenDaysAgo = strtotime('-14 days');
        
        if ($totalActivities < 3 && $createdAt < $fourteenDaysAgo) {
            $riskScore += 1;
        }
        
        return min($riskScore, 3);
    }

    /**
     * ✅ CAMBIADO: Identifica factores usando DAO
     */
    private function identifyRiskFactors($student)
    {
        $factors = [];
        
        // Inactividad
        $lastActivity = $student->last_activity ?? null;
        
        if (!$lastActivity) {
            $factors[] = 'Nunca ha ingresado a la plataforma';
        } elseif (strtotime($lastActivity) < strtotime('-14 days')) {
            $factors[] = 'Inactividad prolongada (más de 14 días)';
        } elseif (strtotime($lastActivity) < strtotime('-7 days')) {
            $factors[] = 'Inactividad moderada (más de 7 días)';
        }
        
        // Rendimiento - usar DAO
        $performance = $this->diagnosticResponseDAO->getStudentPerformance($student->id);
        $avgScore = $performance['percentage'];
        
        if ($avgScore > 0 && $avgScore < 50) {
            $factors[] = 'Rendimiento muy bajo (promedio < 50%)';
        } elseif ($avgScore > 0 && $avgScore < 70) {
            $factors[] = 'Rendimiento bajo (promedio < 70%)';
        }
        
        // Participación - usar DAO
        $totalActivities = $this->diagnosticResponseDAO->countCompletedDiagnostics($student->id);
        
        $createdAt = strtotime($student->created_at);
        $sevenDaysAgo = strtotime('-7 days');
        $fourteenDaysAgo = strtotime('-14 days');
        
        if ($totalActivities === 0 && $createdAt < $sevenDaysAgo) {
            $factors[] = 'Sin actividad registrada';
        } elseif ($totalActivities < 3 && $createdAt < $fourteenDaysAgo) {
            $factors[] = 'Baja participación (menos de 3 actividades)';
        }
        
        if (empty($factors)) {
            $factors[] = 'Requiere seguimiento preventivo';
        }
        
        return $factors;
    }

    /**
     * ✅ CAMBIADO: Enviar email usando DAO para obtener usuario
     */
    public function sendEmail(Request $request, int $userId)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // ✅ USA DAO
        $user = $this->userDAO->findById($userId);

        if (!$user) {
            return redirect()->route('admin.reports.risk')
                ->with('error', 'Usuario no encontrado.');
        }

        try {
            Mail::to($user->email)->send(new \App\Mail\StudentFollowUpMail([
                'subject' => $request->subject,
                'message' => $request->message,
            ]));

            Log::info("Email enviado a {$user->email}", [
                'admin_id' => Auth::id(),
                'admin_name' => Auth::user()->name,
                'subject' => $request->subject,
                'sent_at' => now(),
            ]);

            return redirect()->route('admin.reports.risk')
                ->with('success', "✓ Email enviado exitosamente a {$user->name}");

        } catch (\Exception $e) {
            Log::error("Error al enviar email a {$user->email}: " . $e->getMessage());
            
            return redirect()->route('admin.reports.risk')
                ->with('error', "Error al enviar el email. Por favor, intenta nuevamente.");
        }
    }

    /**
     * ✅ CAMBIADO: Agendar seguimiento usando DAOs
     */
    public function scheduleFollowUp(Request $request, int $userId)
    {
        $request->validate([
            'type' => 'required|string|in:meeting,call,video_call,email',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required',
            'notes' => 'nullable|string|max:1000',
        ]);

        // ✅ USA DAO
        $user = $this->userDAO->findById($userId);

        if (!$user) {
            return redirect()->route('admin.reports.risk')
                ->with('error', 'Usuario no encontrado.');
        }

        try {
            // ✅ USA DAO para crear seguimiento
            $followUpId = $this->followUpDAO->create([
                'user_id' => $user->id,
                'admin_id' => Auth::id(),
                'type' => $request->type,
                'scheduled_at' => $request->date . ' ' . $request->time,
                'notes' => $request->notes,
            ]);

            if (!$followUpId) {
                throw new \Exception('No se pudo crear el seguimiento');
            }

            // Obtener el seguimiento creado
            $followUp = $this->followUpDAO->findById($followUpId);

            Log::info("Seguimiento agendado para {$user->name}", [
                'follow_up_id' => $followUpId,
                'admin_name' => Auth::user()->name,
                'scheduled_at' => $followUp->scheduled_at ?? ($request->date . ' ' . $request->time),
            ]);

            return redirect()->route('admin.reports.risk')
                ->with('success', "✓ Seguimiento agendado para {$user->name} el " . 
                    date('d/m/Y \a \l\a\s H:i', strtotime($request->date . ' ' . $request->time)));

        } catch (\Exception $e) {
            Log::error("Error al agendar seguimiento: " . $e->getMessage());
            
            return redirect()->route('admin.reports.risk')
                ->with('error', "Error al agendar el seguimiento. Por favor, intenta nuevamente.");
        }
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataAccessModels\UsuarioModel;
use App\DataAccessModels\SeguimientoModel;
use App\DataAccessModels\DiagnosticoModel;
use App\DataAccessModels\ProgresoModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DashboardController extends Controller
{
    protected $usuarioModel;
    protected $seguimientoModel;
    protected $diagnosticoModel;
    protected $progresoModel;

    /**
     * Constructor - Inyección de modelos PDO
     */
    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
        $this->seguimientoModel = new SeguimientoModel();
        $this->diagnosticoModel = new DiagnosticoModel();
        $this->progresoModel = new ProgresoModel();
    }

    /**
     * Dashboard principal del administrador
     */
    
    public function index()
    {
        // ✅ Estadísticas generales usando SPs
        $totalUsers = $this->usuarioModel->contarPorRol(null); // Todos los usuarios
        $totalAdmins = $this->usuarioModel->contarPorRol(1); // Administradores
        $totalTeachers = $this->usuarioModel->contarPorRol(2); // Docentes
        $totalStudents = $this->usuarioModel->contarPorRol(3); // Estudiantes
        $pendingUsersCount = $this->usuarioModel->contarPorRol(4); // Usuarios pendientes de rol
        
        // ✅ Nuevos usuarios (últimos 7 días)
        $newUsersThisWeek = $this->usuarioModel->contarNuevosUsuarios(7);
        
        // ✅ Nuevos usuarios este mes
        $newUsersThisMonth = $this->usuarioModel->contarNuevosUsuarios(30);
        
        // ✅ Nuevos usuarios mes anterior
        $newUsersLastMonth = $this->usuarioModel->contarNuevosUsuariosMesAnterior();
        
        // ✅ Actividades completadas
        $completedActivities = $this->progresoModel->obtenerTotalActividadesCompletadas();
        
        // ✅ Diagnósticos completados hoy
        $diagnosticsCompletedToday = $this->diagnosticoModel->contarDiagnosticosHoy();
        
        // ✅ Estudiantes en riesgo
        $studentsData = $this->usuarioModel->obtenerEstudiantes();
        $students = collect($studentsData)->map(fn($s) => (object) $s);
        
        $atRiskCount = $students->filter(function($student) {
            return $this->calculateRiskLevel($student) >= 2; // Nivel medio-alto
        })->count();
        
        // ✅ Datos para gráficos
        $performanceBySubject = $this->diagnosticoModel->obtenerRendimientoPorMateria();
        $performanceByMonth = $this->diagnosticoModel->obtenerRendimientoPorMes();
        
        // ✅ Actividad reciente (últimos estudiantes activos)
        $recentActivity = collect($studentsData)
            ->map(fn($s) => (object) $s)
            ->sortByDesc('last_activity')
            ->take(10)
            ->values();
        
        // Datos básicos del dashboard (para compatibilidad)
        $riskAlerts = collect([]);
        $mlAlerts = null;
        $mlAnalysis = null;
        $mlRecommendations = null;
        $overallProgress = 0;
        $learningPaths = collect([]);
        $recommendations = collect([]);
        $subjectProgress = collect([]);
        
        return view('admin.dashboard', [
            // Estadísticas principales
            'totalUsers' => $totalUsers,
            'totalAdmins' => $totalAdmins,
            'totalTeachers' => $totalTeachers,
            'totalStudents' => $totalStudents,
            'pendingUsersCount' => $pendingUsersCount,
            'newUsersThisWeek' => $newUsersThisWeek,
            'newUsersThisMonth' => $newUsersThisMonth,
            'newUsersLastMonth' => $newUsersLastMonth,
            'completedActivities' => $completedActivities,
            'diagnosticsCompletedToday' => $diagnosticsCompletedToday,
            'atRiskCount' => $atRiskCount,
            
            // Datos para gráficos
            'performanceBySubject' => $performanceBySubject,
            'performanceByMonth' => $performanceByMonth,
            'recentActivity' => $recentActivity,
            
            // Datos básicos (compatibilidad)
            'riskAlerts' => $riskAlerts,
            'mlAlerts' => $mlAlerts,
            'mlAnalysis' => $mlAnalysis,
            'mlRecommendations' => $mlRecommendations,
            'overallProgress' => $overallProgress,
            'learningPaths' => $learningPaths,
            'recommendations' => $recommendations,
            'subjectProgress' => $subjectProgress,
        ]);
    }

    /**
     * Vista principal de reportes
     */
    public function reports()
    {
        // ✅ Usar stored procedures para estadísticas
        $totalStudents = $this->usuarioModel->contarPorRol(3); // 3 = estudiante
        $activeTeachers = $this->usuarioModel->contarPorRol(2); // 2 = docente
        $completedActivities = $this->progresoModel->obtenerTotalActividadesCompletadas();
        $averageSatisfaction = 89; // Valor estático o desde encuestas
        
        return view('admin.reports.index', [
            'totalStudents' => $totalStudents,
            'activeTeachers' => $activeTeachers,
            'completedActivities' => $completedActivities,
            'averageSatisfaction' => $averageSatisfaction
        ]);
    }

    /**
     * Reportes de estudiantes con filtros
     */
    public function studentReports(Request $request)
    {
        // ✅ Obtener estudiantes filtrados usando SP
        $studentsData = $this->usuarioModel->obtenerEstudiantesFiltros(
            $request->search,
            $request->status
        );
        
        // Convertir array a collection
        $studentsCollection = collect($studentsData);
        
        // ✅ Enriquecer datos de cada estudiante usando SPs
        $studentsEnriched = $studentsCollection->map(function($student) {
            $student = (object) $student;
            
            // ✅ Obtener métricas usando stored procedures
            $student->total_diagnostics = $this->diagnosticoModel->contarDiagnosticosCompletados($student->id);
            $student->completed_activities = $this->usuarioModel->contarActividadesCompletadas($student->id);
            
            // ✅ Obtener rendimiento usando SP
            $performance = $this->diagnosticoModel->obtenerRendimientoEstudiante($student->id);
            $student->average_score = $performance['percentage'];
            
            // Calcular nivel de riesgo
            $student->risk_level = $this->calculateRiskLevel($student);
            
            return $student;
        });
        
        // Crear paginación manual
        $perPage = 20;
        $currentPage = $request->input('page', 1);
        $students = new \Illuminate\Pagination\LengthAwarePaginator(
            $studentsEnriched->forPage($currentPage, $perPage),
            $studentsEnriched->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        return view('admin.reports.students', ['students' => $students
        ]);
    }

    /**
     * Reportes de rendimiento académico
     */
    public function performanceReports(Request $request)
    {
        // ✅ Rendimiento por materia usando SP
        $performanceBySubject = $this->diagnosticoModel->obtenerRendimientoPorMateria();
        
        // ✅ Top estudiantes usando SPs
        $allStudents = $this->usuarioModel->obtenerEstudiantes();
        
        $topStudents = collect($allStudents)->map(function($student) {
            $student = (object) $student;
            
            // ✅ Obtener rendimiento usando SP
            $performance = $this->diagnosticoModel->obtenerRendimientoEstudiante($student->id);
            
            $student->total_responses = $performance['total_responses'];
            $student->correct_responses = $performance['correct_responses'];
            $student->average_score = $performance['percentage'];
            
            return $student;
        })
        ->filter(function($student) {
            return $student->total_responses > 0;
        })
        ->sortByDesc('average_score')
        ->take(10)
        ->values();
        
        // ✅ Rendimiento por mes usando SP
        $performanceByMonth = $this->diagnosticoModel->obtenerRendimientoPorMes();
        
        return view('admin.reports.performance', [
            'performanceBySubject' => $performanceBySubject,
            'topStudents' => $topStudents,
            'performanceByMonth' => $performanceByMonth
        ]);
    }

    /**
     * Reportes de estudiantes en riesgo
     */
    public function riskReports()
    {
        // ✅ Obtener todos los estudiantes usando SP
        $studentsData = $this->usuarioModel->obtenerEstudiantes();
        $students = collect($studentsData)->map(fn($s) => (object) $s);
        
        // Filtrar estudiantes en riesgo
        $atRiskStudents = $students->filter(function($student) {
            return $this->calculateRiskLevel($student) >= 1;
        })->sortByDesc(function($student) {
            return $this->calculateRiskLevel($student);
        })->values();
        
        // Agregar información de riesgo
        foreach ($atRiskStudents as $student) {
            $student->risk_level = $this->calculateRiskLevel($student);
            $student->risk_factors = $this->identifyRiskFactors($student);
        }
        
        return view('admin.reports.risk', ['atRiskStudents' => $atRiskStudents]);
    }

    /**
     * Generar reporte descargable
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'report_type' => 'required|string',
            'period' => 'required|string',
            'format' => 'required|in:pdf,excel,csv',
        ]);
        
        // TODO: Implementar generación de reportes
        
        return redirect()->route('admin.reports.index')
            ->with('success', 'Reporte generado exitosamente. (Función en desarrollo)');
    }

    /**
     * Monitoreo del sistema
     */
    public function systemMonitoring()
    {
        return view('admin.monitoring.index');
    }

    /**
     * Estadísticas de uso
     */
    public function usageStats()
    {
        return view('admin.monitoring.usage');
    }

    // ==================== MÉTODOS AUXILIARES ====================

    /**
 * Calcular nivel de riesgo de un estudiante
 * @param object $student
 * @return int 0-3 (0=sin riesgo, 3=riesgo alto)
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
    
    // Factor 2: Bajo rendimiento académico (usando SP)
    $performance = $this->diagnosticoModel->obtenerRendimientoEstudiante($student->id);
    $avgScore = $performance['percentage'] ?? 0;
    
    if ($avgScore > 0 && $avgScore < 50) {
        $riskScore += 2;
    } elseif ($avgScore > 0 && $avgScore < 70) {
        $riskScore += 1;
    }
    
    // Factor 3: Baja participación (usando SP)
    $totalActivities = $this->diagnosticoModel->contarDiagnosticosCompletados($student->id);
    
    // Verificar si existe created_at antes de usarlo
    if (isset($student->created_at)) {
        $createdAt = strtotime($student->created_at);
        $fourteenDaysAgo = strtotime('-14 days');
        
        if ($totalActivities < 3 && $createdAt < $fourteenDaysAgo) {
            $riskScore += 1;
        }
    }
    
    return ($riskScore > 3) ? 3 : $riskScore;
}

    /**
 * Identificar factores de riesgo específicos
 * @param object $student
 * @return array
 */
private function identifyRiskFactors($student)
{
    $factors = [];
    
    // Factor: Inactividad
    $lastActivity = $student->last_activity ?? null;
    
    if (!$lastActivity) {
        $factors[] = 'Nunca ha ingresado a la plataforma';
    } elseif (strtotime($lastActivity) < strtotime('-14 days')) {
        $factors[] = 'Inactividad prolongada (más de 14 días)';
    } elseif (strtotime($lastActivity) < strtotime('-7 days')) {
        $factors[] = 'Inactividad moderada (más de 7 días)';
    }
    
    // Factor: Rendimiento académico (usando SP)
    $performance = $this->diagnosticoModel->obtenerRendimientoEstudiante($student->id);
    $avgScore = $performance['percentage'] ?? 0;
    
    if ($avgScore > 0 && $avgScore < 50) {
        $factors[] = 'Rendimiento muy bajo (promedio < 50%)';
    } elseif ($avgScore > 0 && $avgScore < 70) {
        $factors[] = 'Rendimiento bajo (promedio < 70%)';
    }
    
    // Factor: Participación (usando SP)
    $totalActivities = $this->diagnosticoModel->contarDiagnosticosCompletados($student->id);
    
    // Verificar si existe created_at antes de usarlo
    if (isset($student->created_at)) {
        $createdAt = strtotime($student->created_at);
        $sevenDaysAgo = strtotime('-7 days');
        $fourteenDaysAgo = strtotime('-14 days');
        
        if ($totalActivities === 0 && $createdAt < $sevenDaysAgo) {
            $factors[] = 'Sin actividad registrada';
        } elseif ($totalActivities < 3 && $createdAt < $fourteenDaysAgo) {
            $factors[] = 'Baja participación (menos de 3 actividades)';
        }
    } else {
        // Si no hay created_at pero no tiene actividades
        if ($totalActivities === 0) {
            $factors[] = 'Sin actividad registrada';
        } elseif ($totalActivities < 3) {
            $factors[] = 'Baja participación (menos de 3 actividades)';
        }
    }
    
    if (empty($factors)) {
        $factors[] = 'Requiere seguimiento preventivo';
    }
    
    return $factors;
}

    /**
     * Enviar email de seguimiento a estudiante
     */
    public function sendEmail(Request $request, int $userId)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // ✅ Obtener usuario usando SP
        $userData = $this->usuarioModel->obtenerUsuario($userId);

        if (!$userData) {
            return redirect()->route('admin.reports.risk')
                ->with('error', 'Usuario no encontrado.');
        }

        $user = (object) $userData;

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
     * Agendar seguimiento con estudiante
     */
    public function scheduleFollowUp(Request $request, int $userId)
    {
        $request->validate([
            'type' => 'required|string|in:meeting,call,video_call,email',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required',
            'notes' => 'nullable|string|max:1000',
        ]);

        // ✅ Verificar que el usuario existe usando SP
        $userData = $this->usuarioModel->obtenerUsuario($userId);

        if (!$userData) {
            return redirect()->route('admin.reports.risk')
                ->with('error', 'Usuario no encontrado.');
        }

        $user = (object) $userData;

        try {
            // ✅ Crear seguimiento usando SP
            $followUpId = $this->seguimientoModel->crear([
                'user_id' => $user->id,
                'admin_id' => Auth::id(),
                'type' => $request->type,
                'scheduled_at' => $request->date . ' ' . $request->time,
                'notes' => $request->notes,
            ]);

            if (!$followUpId) {
                throw new \Exception('No se pudo crear el seguimiento');
            }

            Log::info("Seguimiento agendado para {$user->name}", [
                'follow_up_id' => $followUpId,
                'admin_name' => Auth::user()->name,
                'scheduled_at' => $request->date . ' ' . $request->time,
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
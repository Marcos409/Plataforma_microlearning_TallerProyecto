<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataAccessModels\UsuarioModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SystemMonitoringController extends Controller
{
    protected $usuarioModel;
    
    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
    }
    
    public function index()
    {

        
        try {
            // === ESTADÍSTICAS PRINCIPALES ===
            $estadisticas = $this->usuarioModel->obtenerEstadisticasGenerales();
            
            $stats = [
                'usuarios_activos' => $this->getUsuariosActivos24h(),
                'sesiones_hoy' => $this->getSesionesHoy(),
                'actividades_completadas' => $this->getActividadesCompletadas(),
                'tiempo_promedio_sesion' => $this->getTiempoPromedioSesion()
            ];

            // === USUARIOS ACTIVOS RECIENTES ===
            $usuarios_activos = $this->getUsuariosRecientes();

            // === ACTIVIDAD POR HORA (HOY) ===
            $actividad_por_hora = $this->getActivityByHour();

            // === DISTRIBUCIÓN DE USUARIOS POR ROL ===
            $distribucion_usuarios = [
                $estadisticas['total_estudiantes'],
                $estadisticas['total_docentes'],
                $estadisticas['total_admins']
            ];

            // === CONTENIDO MÁS ACCEDIDO ===
            $contenido_mas_accedido = $this->getContenidoMasAccedido();

            // === ACTIVIDAD SEMANAL ===
            $actividad_semanal = $this->getWeeklyActivity();

            return view('admin.monitoring.system-monitoring', compact(
                'stats',
                'usuarios_activos',
                'actividad_por_hora',
                'distribucion_usuarios',
                'contenido_mas_accedido',
                'actividad_semanal'
            ));

        } catch (\Exception $e) {
            \Log::error('Error en System Monitoring: ' . $e->getMessage());
            
            return view('admin.monitoring.system-monitoring', [
                'stats' => [
                    'usuarios_activos' => 0,
                    'sesiones_hoy' => 0,
                    'actividades_completadas' => 0,
                    'tiempo_promedio_sesion' => 0
                ],
                'usuarios_activos' => [],
                'actividad_por_hora' => ['labels' => [], 'data' => []],
                'distribucion_usuarios' => [0, 0, 0],
                'contenido_mas_accedido' => [],
                'actividad_semanal' => ['labels' => [], 'data' => []]
            ]);
        }
    }

    /**
     * Obtener usuarios activos en las últimas 24 horas
     */
    private function getUsuariosActivos24h()
    {
        try {
            $result = DB::selectOne("
                SELECT COUNT(*) as total
                FROM users
                WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND active = 1
            ");
            
            return (int)($result->total ?? 0);
        } catch (\Exception $e) {
            \Log::error('Error en getUsuariosActivos24h: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener sesiones de hoy
     */
    private function getSesionesHoy()
    {
        try {
            $result = DB::selectOne("
                SELECT COUNT(*) as total
                FROM users
                WHERE DATE(updated_at) = CURDATE()
                AND active = 1
            ");
            
            return (int)($result->total ?? 0);
        } catch (\Exception $e) {
            \Log::error('Error en getSesionesHoy: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener actividades completadas esta semana
     */
    private function getActividadesCompletadas()
    {
        try {
            // Usar student_progress en lugar de user_progress
            $result = DB::selectOne("
                SELECT SUM(completed_activities) as total
                FROM student_progress
                WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            
            return (int)($result->total ?? 0);
        } catch (\Exception $e) {
            \Log::error('Error en getActividadesCompletadas: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcular tiempo promedio de sesión basado en student_progress
     */
    private function getTiempoPromedioSesion()
    {
        try {
            $result = DB::selectOne("
                SELECT AVG(total_time_spent) as promedio
                FROM student_progress
                WHERE total_time_spent > 0
                AND updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            
            return (int)($result->promedio ?? 45);
        } catch (\Exception $e) {
            return 45;
        }
    }

    /**
     * Obtener usuarios recientes con actividad
     */
    private function getUsuariosRecientes()
    {
        try {
            $usuarios = DB::select("
                SELECT 
                    u.id,
                    u.name,
                    u.email,
                    u.role_id,
                    r.name as role_name,
                    u.updated_at as last_activity,
                    u.active,
                    COUNT(DISTINCT sp.id) as sesiones_hoy,
                    COALESCE(SUM(sp.total_time_spent), 0) as tiempo_total
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                LEFT JOIN student_progress sp ON u.id = sp.user_id 
                    AND DATE(sp.updated_at) = CURDATE()
                WHERE u.active = 1
                GROUP BY u.id, u.name, u.email, u.role_id, r.name, u.updated_at, u.active
                ORDER BY u.updated_at DESC
                LIMIT 10
            ");
            
            return $usuarios;
        } catch (\Exception $e) {
            \Log::error('Error en getUsuariosRecientes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene actividad por hora del día actual
     */
    private function getActivityByHour()
    {
        $hours = [];
        $data = [];

        for ($i = 0; $i < 24; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
            $hours[] = $hour;

            try {
                // Usar student_progress para actividad
                $result = DB::selectOne("
                    SELECT COUNT(DISTINCT user_id) as total
                    FROM student_progress
                    WHERE DATE(updated_at) = CURDATE()
                    AND HOUR(updated_at) = ?
                ", [$i]);
                
                $count = (int)($result->total ?? 0);
                
                // Si no hay datos en student_progress, usar users
                if ($count === 0) {
                    $result = DB::selectOne("
                        SELECT COUNT(*) as total
                        FROM users
                        WHERE DATE(updated_at) = CURDATE()
                        AND HOUR(updated_at) = ?
                        AND active = 1
                    ", [$i]);
                    
                    $count = (int)($result->total ?? 0);
                }
                
                $data[] = $count;
            } catch (\Exception $e) {
                $data[] = 0;
            }
        }

        return [
            'labels' => $hours,
            'data' => $data
        ];
    }

    /**
     * Obtiene actividad de la última semana
     */
    private function getWeeklyActivity()
    {
        $labels = [];
        $data = [];
        $daysOfWeek = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dayOfWeek = date('w', strtotime($date));
            $labels[] = $daysOfWeek[$dayOfWeek];

            try {
                // Contar actividades completadas ese día
                $result = DB::selectOne("
                    SELECT SUM(completed_activities) as total
                    FROM student_progress
                    WHERE DATE(updated_at) = ?
                ", [$date]);
                
                $count = (int)($result->total ?? 0);
                
                // Si no hay datos, usar cantidad de usuarios activos
                if ($count === 0) {
                    $result = DB::selectOne("
                        SELECT COUNT(DISTINCT user_id) as total
                        FROM student_progress
                        WHERE DATE(updated_at) = ?
                    ", [$date]);
                    
                    $count = (int)($result->total ?? 0);
                }
                
                $data[] = $count;
            } catch (\Exception $e) {
                $data[] = 0;
            }
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Obtener contenido más accedido
     */
    private function getContenidoMasAccedido()
    {
        try {
            $contenidos = DB::select("
                SELECT 
                    c.id,
                    c.title,
                    c.type as content_type,
                    c.views as vistas
                FROM content_library c
                WHERE c.active = 1
                ORDER BY c.views DESC
                LIMIT 5
            ");
            
            return $contenidos;
        } catch (\Exception $e) {
            \Log::error('Error en getContenidoMasAccedido: ' . $e->getMessage());
            return [];
        }
    }
}
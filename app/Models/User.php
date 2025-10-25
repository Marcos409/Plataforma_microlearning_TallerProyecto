<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Role;

/**
 * User Model - Solo estructura y relaciones
 * Todas las queries se hacen a través de UserDAO
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\StudentProgress[] $studentProgress
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LearningPath[] $learningPaths
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\RiskAlert[] $riskAlerts
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'student_code',
        'career',
        'semester',
        'phone',
        'role_id',
        'active',
        'last_activity',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean',
        'semester' => 'integer',
        'last_activity' => 'datetime',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['role'];

    // ==================== RELACIONES ====================
    // Las relaciones de Eloquent se mantienen porque definen la estructura

    /**
     * Relación con el modelo Role
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relación con el progreso del estudiante
     */
    public function studentProgress()
    {
        return $this->hasMany(StudentProgress::class);
    }

    /**
     * Relación con las rutas de aprendizaje
     */
    public function learningPaths()
    {
        return $this->hasMany(LearningPath::class);
    }

    /**
     * Relación con las recomendaciones
     */
    public function recommendations()
    {
        return $this->hasMany(Recommendation::class);
    }

    /**
     * Relación con las alertas de riesgo
     */
    public function riskAlerts()
    {
        return $this->hasMany(RiskAlert::class);
    }

    /**
     * Relación con las respuestas de diagnósticos
     */
    public function diagnosticResponses()
    {
        return $this->hasMany(DiagnosticResponse::class);
    }

    /**
     * Relación con los seguimientos agendados
     */
    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    // ==================== MÉTODOS AUXILIARES SIMPLES ====================
    // Solo métodos que NO hacen queries a la BD

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->role_id == 1;
    }

    /**
     * Verificar si el usuario es docente
     */
    public function isTeacher(): bool
    {
        return $this->role_id == 2;
    }

    /**
     * Verificar si el usuario es estudiante
     */
    public function isStudent(): bool
    {
        return $this->role_id == 3;
    }

    /**
     * Verificar si el usuario está activo
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    // ==================== SCOPES ====================
    // Los scopes se mantienen porque Eloquent los necesita para queries básicas

    /**
     * Scope para filtrar usuarios activos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para filtrar por rol
     */
    public function scopeByRole($query, $roleName)
    {
        return $query->whereHas('role', function($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /**
     * Scope para filtrar estudiantes
     */
    public function scopeStudents($query)
    {
        return $query->where('role_id', 3);
    }

    /**
     * Scope para filtrar docentes
     */
    public function scopeTeachers($query)
    {
        return $query->where('role_id', 2);
    }

    /**
     * Scope para filtrar administradores
     */
    public function scopeAdmins($query)
    {
        return $query->where('role_id', 1);
    }

    // ==================== NOTA IMPORTANTE ====================
    /**
     * MÉTODOS ELIMINADOS - Ahora están en UserDAO:
     * 
     * - getOverallProgress() → UserDAO::getOverallProgress($userId)
     * - getTotalTimeSpent() → UserDAO::getTotalTimeSpent($userId)
     * - getCompletedActivitiesCount() → UserDAO::getCompletedActivitiesCount($userId)
     * - hasActiveRiskAlerts() → UserDAO::hasActiveRiskAlerts($userId)
     * - getActiveRiskAlerts() → UserDAO::getActiveRiskAlerts($userId)
     * - getPendingRecommendationsCount() → UserDAO::getPendingRecommendationsCount($userId)
     * - updateLastActivity() → UserDAO::updateLastActivity($userId)
     * - isInactiveFor() → UserDAO::isInactiveFor($userId, $days)
     * 
     * USAR: app(UserDAOInterface::class)->metodo($userId)
     * O inyectar UserDAOInterface en el constructor del Controller
     */
}
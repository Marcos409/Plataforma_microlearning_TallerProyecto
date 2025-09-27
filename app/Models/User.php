<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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

    // RELACIONES

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

    // MÉTODOS DE VERIFICACIÓN DE ROLES

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

    // MÉTODOS AUXILIARES

    /**
     * Obtener el nombre del rol
     */
    public function getRoleName(): string
    {
        return $this->role ? $this->role->name : 'Sin rol';
    }

    /**
     * Verificar si el usuario está activo
     */
    public function isActive(): bool
    {
        return $this->active;
    }

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

    /**
     * Obtener el progreso general del estudiante
     */
    public function getOverallProgress(): float
    {
        if (!$this->isStudent()) {
            return 0.0;
        }

        $progress = $this->studentProgress()->avg('progress_percentage');
        return round($progress ?? 0, 2);
    }

    /**
     * Obtener el tiempo total gastado en actividades
     */
    public function getTotalTimeSpent(): int
    {
        if (!$this->isStudent()) {
            return 0;
        }

        return $this->studentProgress()->sum('total_time_spent') ?? 0;
    }

    /**
     * Obtener el número de actividades completadas
     */
    public function getCompletedActivitiesCount(): int
    {
        if (!$this->isStudent()) {
            return 0;
        }

        return $this->studentProgress()->sum('completed_activities') ?? 0;
    }

    /**
     * Verificar si el estudiante tiene alertas de riesgo activas
     */
    public function hasActiveRiskAlerts(): bool
    {
        if (!$this->isStudent()) {
            return false;
        }

        return $this->riskAlerts()->where('is_resolved', false)->exists();
    }

    /**
     * Obtener alertas de riesgo activas
     */
    public function getActiveRiskAlerts()
    {
        return $this->riskAlerts()->where('is_resolved', false)->get();
    }

    /**
     * Obtener el número de recomendaciones pendientes
     */
    public function getPendingRecommendationsCount(): int
    {
        if (!$this->isStudent()) {
            return 0;
        }

        return $this->recommendations()->where('is_completed', false)->count();
    }

    /**
     * Actualizar última actividad
     */
    public function updateLastActivity(): bool
    {
        return $this->update(['last_activity' => now()]);
    }

    /**
     * Verificar si el usuario ha estado inactivo por más de X días
     */
    public function isInactiveFor(int $days): bool
    {
        if (!$this->last_activity) {
            return true;
        }

        return $this->last_activity->diffInDays(now()) > $days;
    }
}
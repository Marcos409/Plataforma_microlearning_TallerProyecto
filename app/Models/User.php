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
    // public function riskAlerts()
    // {
    //     return $this->hasMany(RiskAlert::class);
    // }

    /**
     * Relación con los resultados de diagnósticos
     */
    // public function diagnosticResults()
    // {
    //     return $this->hasMany(DiagnosticResult::class);
    // }

    // MÉTODOS DE VERIFICACIÓN DE ROLES

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin()
    {
        return $this->role && $this->role->name === 'Administrador';
    }

    /**
     * Verificar si el usuario es docente
     */
    public function isTeacher()
    {
        return $this->role && $this->role->name === 'Docente';
    }

    /**
     * Verificar si el usuario es estudiante
     */
    public function isStudent()
    {
        return $this->role && $this->role->name === 'Estudiante';
    }

    // MÉTODOS AUXILIARES

    /**
     * Obtener el nombre del rol
     */
    public function getRoleName()
    {
        return $this->role ? $this->role->name : 'Sin rol';
    }

    /**
     * Verificar si el usuario está activo
     */
    public function isActive()
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
     * Obtener el progreso general del estudiante
     */
    public function getOverallProgress()
    {
        if (!$this->isStudent()) {
            return 0;
        }

        $progress = $this->studentProgress()->avg('progress_percentage');
        return round($progress ?? 0, 2);
    }

    /**
     * Obtener el tiempo total gastado en actividades
     */
    public function getTotalTimeSpent()
    {
        if (!$this->isStudent()) {
            return 0;
        }

        return $this->studentProgress()->sum('total_time_spent');
    }

    /**
     * Obtener el número de actividades completadas
     */
    public function getCompletedActivitiesCount()
    {
        if (!$this->isStudent()) {
            return 0;
        }

        return $this->studentProgress()->sum('completed_activities');
    }

    /**
     * Verificar si el estudiante tiene alertas de riesgo activas
     */
    // public function hasActiveRiskAlerts()
    // {
    //     if (!$this->isStudent()) {
    //         return false;
    //     }

    //     return $this->riskAlerts()->where('is_resolved', false)->exists();
    // }
}
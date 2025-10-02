<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * Relación con usuarios
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Obtener roles disponibles para select
     */
    public static function getSelectOptions()
    {
        return self::pluck('name', 'id')->toArray();
    }

    /**
     * Verificar si es rol de administrador
     */
    public function isAdmin()
    {
        return strtolower($this->name) === 'administrador';
    }

    /**
     * Verificar si es rol de docente
     */
    public function isTeacher()
    {
        return strtolower($this->name) === 'docente';
    }

    /**
     * Verificar si es rol de estudiante
     */
    public function isStudent()
    {
        return strtolower($this->name) === 'estudiante';
    }
}

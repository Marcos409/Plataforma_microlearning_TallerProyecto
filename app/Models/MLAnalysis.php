<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLAnalysis extends Model
{
    protected $table = 'ml_analysis';
    
    protected $fillable = [
        'user_id',
        'diagnostico',
        'ruta_aprendizaje',
        'nivel_riesgo',
        'metricas',
        'recomendaciones'
    ];

    protected $casts = [
        'metricas' => 'array',
        'recomendaciones' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
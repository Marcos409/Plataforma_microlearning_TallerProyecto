<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_id',
        'type',
        'scheduled_at',
        'notes',
        'completed',
        'reminder_sent',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed' => 'boolean',
        'reminder_sent' => 'boolean',
    ];

    /**
     * Relación con el estudiante
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el administrador que agendó
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Scope para seguimientos pendientes
     */
    public function scopePending($query)
    {
        return $query->where('completed', false)
                     ->where('scheduled_at', '>=', now());
    }

    /**
     * Scope para seguimientos vencidos
     */
    public function scopeOverdue($query)
    {
        return $query->where('completed', false)
                     ->where('scheduled_at', '<', now());
    }

    /**
     * Obtener el tipo de seguimiento en español
     */
    public function getTypeNameAttribute()
    {
        $types = [
            'meeting' => 'Reunión presencial',
            'call' => 'Llamada telefónica',
            'video_call' => 'Videollamada',
            'email' => 'Seguimiento por email',
        ];

        return $types[$this->type] ?? $this->type;
    }
}
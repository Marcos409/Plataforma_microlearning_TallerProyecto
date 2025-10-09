<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiagnosticResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'diagnostic_id',
        'question_id',
        'user_answer',        // Cambiado de 'selected_answer'
        'is_correct',
        'points_earned',      // Agregado
        'time_spent_seconds'  // Cambiado de 'time_spent'
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function diagnostic()
    {
        return $this->belongsTo(Diagnostic::class);
    }

    public function question()
    {
        return $this->belongsTo(DiagnosticQuestion::class, 'question_id');
    }
}

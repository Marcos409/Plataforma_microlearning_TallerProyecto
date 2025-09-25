<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiagnosticQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'diagnostic_id', 'question', 'options', 'correct_answer', 
        'difficulty_level', 'topic'
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function diagnostic()
    {
        return $this->belongsTo(Diagnostic::class);
    }

    public function responses()
    {
        return $this->hasMany(DiagnosticResponse::class, 'question_id');
    }
}
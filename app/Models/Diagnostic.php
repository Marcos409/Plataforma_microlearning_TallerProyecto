<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diagnostic extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'subject_area', 'total_questions', 
        'passing_score', 'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function questions()
    {
        return $this->hasMany(DiagnosticQuestion::class);
    }

    public function responses()
    {
        return $this->hasMany(DiagnosticResponse::class);
    }

    public function updateTotalQuestions()
    {
        $this->total_questions = $this->questions()->count();
        $this->save();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProgress extends Model
{
    use HasFactory;

    protected $table = 'student_progress';

    protected $fillable = [
        'user_id', 'subject_area', 'topic', 'total_activities', 
        'completed_activities', 'progress_percentage', 'average_score',
        'total_time_spent', 'last_activity', 'weak_areas'
    ];

    protected $casts = [
        'progress_percentage' => 'decimal:2',
        'average_score' => 'decimal:2',
        'weak_areas' => 'array',
        'last_activity' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
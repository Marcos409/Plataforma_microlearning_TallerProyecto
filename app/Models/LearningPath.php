<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningPath extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'description', 'subject_area', 'critical_topics',
        'total_contents', 'completed_contents', 'progress_percentage', 
        'status', 'estimated_completion'
    ];

    protected $casts = [
        'critical_topics' => 'array',
        'progress_percentage' => 'decimal:2',
        'estimated_completion' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contents()
    {
        return $this->hasMany(LearningPathContent::class);
    }

    public function updateProgress()
    {
        $total = $this->contents()->count();
        $completed = $this->contents()->where('is_completed', true)->count();
        
        $this->total_contents = $total;
        $this->completed_contents = $completed;
        $this->progress_percentage = $total > 0 ? ($completed / $total) * 100 : 0;
        
        if ($this->progress_percentage >= 100) {
            $this->status = 'completed';
        }
        
        $this->save();
    }
}
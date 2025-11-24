<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningPathContent extends Model
{
    protected $table = 'learning_path_content';
    use HasFactory;

    protected $fillable = [
        'learning_path_id', 'content_id', 'order_sequence', 
        'is_completed', 'completed_at', 'time_spent'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function learningPath()
    {
        return $this->belongsTo(LearningPath::class);
    }

    public function content()
    {
        return $this->belongsTo(ContentLibrary::class, 'content_id');
    }
}
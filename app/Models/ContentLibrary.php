<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentLibrary extends Model
{
    use HasFactory;

    protected $table = 'content_library';

    protected $fillable = [
        'title', 'description', 'type', 'subject_area', 'topic', 
        'difficulty_level', 'file_path', 'external_url', 
        'estimated_duration', 'tags', 'active'
    ];

    protected $casts = [
        'tags' => 'array',
        'active' => 'boolean',
    ];

    public function learningPathContents()
    {
        return $this->hasMany(LearningPathContent::class, 'content_id');
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class, 'content_id');
    }
}
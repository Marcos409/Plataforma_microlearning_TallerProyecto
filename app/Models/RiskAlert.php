<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'title', 'description', 'severity',
        'affected_topics', 'is_resolved', 'resolved_at', 
        'resolved_by', 'resolution_notes'
    ];

    protected $casts = [
        'affected_topics' => 'array',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
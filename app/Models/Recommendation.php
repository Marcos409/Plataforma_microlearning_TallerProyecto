<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'content_id', 'type', 'reason', 'priority',
        'is_viewed', 'is_completed', 'viewed_at', 'completed_at'
    ];

    protected $casts = [
        'is_viewed' => 'boolean',
        'is_completed' => 'boolean',
        'viewed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function content()
    {
        return $this->belongsTo(ContentLibrary::class, 'content_id');
    }
}

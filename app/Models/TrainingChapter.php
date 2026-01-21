<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingChapter extends Model
{
    protected $fillable = [
        'title',
        'description',
        'order',
        'is_active',
        'created_by',
        'organization_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function videos()
    {
        return $this->hasMany(TrainingVideo::class, 'chapter_id')->where('is_active', true)->orderBy('order');
    }

    public function notes()
    {
        return $this->hasMany(TrainingNote::class, 'chapter_id')->where('is_active', true)->orderBy('order');
    }

    public function quizzes()
    {
        return $this->hasMany(TrainingQuiz::class, 'chapter_id')->where('is_active', true)->orderBy('order');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
} 
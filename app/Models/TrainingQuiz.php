<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingQuiz extends Model
{
    protected $fillable = [
        'chapter_id',
        'title',
        'description',
        'time_limit',
        'passing_score',
        'order',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'time_limit' => 'integer',
        'passing_score' => 'integer',
    ];

    public function chapter()
    {
        return $this->belongsTo(TrainingChapter::class, 'chapter_id');
    }

    public function questions()
    {
        return $this->hasMany(TrainingQuizQuestion::class, 'quiz_id')->where('is_active', true)->orderBy('order');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function userProgress()
    {
        return $this->hasMany(TrainingUserProgress::class, 'quiz_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTotalPointsAttribute()
    {
        return $this->questions->sum('points');
    }

    public function getQuestionCountAttribute()
    {
        return $this->questions->count();
    }

    public function getFormattedTimeLimitAttribute()
    {
        if (!$this->time_limit) {
            return 'No time limit';
        }

        $hours = floor($this->time_limit / 60);
        $minutes = $this->time_limit % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }

        return $minutes . ' minutes';
    }
} 
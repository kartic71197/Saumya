<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingUserProgress extends Model
{
    protected $table = 'training_user_progress';

    protected $fillable = [
        'user_id',
        'video_id',
        'quiz_id',
        'status',
        'progress_percentage',
        'score',
        'started_at',
        'completed_at',
        'quiz_answers',
    ];

    protected $casts = [
        'progress_percentage' => 'integer',
        'score' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'quiz_answers' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function video()
    {
        return $this->belongsTo(TrainingVideo::class, 'video_id');
    }

    public function quiz()
    {
        return $this->belongsTo(TrainingQuiz::class, 'quiz_id');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForVideo($query, $videoId)
    {
        return $query->where('video_id', $videoId);
    }

    public function scopeForQuiz($query, $quizId)
    {
        return $query->where('quiz_id', $quizId);
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'not_started' => 'Not Started',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'failed' => 'Failed',
        ];

        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'not_started' => 'gray',
            'in_progress' => 'blue',
            'completed' => 'green',
            'failed' => 'red',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }
} 
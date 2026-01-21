<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingQuizQuestion extends Model
{
    protected $fillable = [
        'quiz_id',
        'question',
        'question_type',
        'options',
        'correct_answer',
        'explanation',
        'points',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'options' => 'array',
        'points' => 'integer',
    ];

    public function quiz()
    {
        return $this->belongsTo(TrainingQuiz::class, 'quiz_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getOptionsArrayAttribute()
    {
        return $this->options ?? [];
    }

    public function isCorrect($answer)
    {
        return strtolower(trim($answer)) === strtolower(trim($this->correct_answer));
    }

    public function getQuestionTypeLabelAttribute()
    {
        $types = [
            'multiple_choice' => 'Multiple Choice',
            'true_false' => 'True/False',
            'essay' => 'Essay',
        ];

        return $types[$this->question_type] ?? 'Unknown';
    }
} 
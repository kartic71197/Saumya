<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingVideo extends Model
{
    protected $fillable = [
        'chapter_id',
        'title',
        'description',
        'video_url',
        'thumbnail_url',
        'duration',
        'order',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration' => 'integer',
    ];

    public function chapter()
    {
        return $this->belongsTo(TrainingChapter::class, 'chapter_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function userProgress()
    {
        return $this->hasMany(TrainingUserProgress::class, 'video_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedDurationAttribute()
    {
        if (!$this->duration) {
            return 'Unknown';
        }

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }
} 
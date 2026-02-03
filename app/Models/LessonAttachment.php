<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonAttachment extends Model
{
    protected $fillable = [
        'lesson_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size'
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}

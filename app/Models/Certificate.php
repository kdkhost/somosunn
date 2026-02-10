<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'course_id', 'mentorship_id', 'event_id', 'cert_hash', 'pdf_path', 'issued_at'];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function mentorship()
    {
        return $this->belongsTo(Mentorship::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
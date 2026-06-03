<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','enrollable_id','enrollable_type','status','progress','started_at','completed_at'];

    protected $casts = [
        'progress' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrollable()
    {
        return $this->morphTo();
    }

    public function scopeWithoutCertificate($query)
    {
        return $query->whereNotExists(function ($certificateQuery) {
            $certificateQuery
                ->select(DB::raw(1))
                ->from('certificates')
                ->whereColumn('certificates.user_id', 'enrollments.user_id')
                ->where(function ($typeQuery) {
                    $typeQuery
                        ->where(function ($query) {
                            $query->where('enrollments.enrollable_type', Course::class)
                                ->whereColumn('certificates.course_id', 'enrollments.enrollable_id');
                        })
                        ->orWhere(function ($query) {
                            $query->where('enrollments.enrollable_type', Mentorship::class)
                                ->whereColumn('certificates.mentorship_id', 'enrollments.enrollable_id');
                        })
                        ->orWhere(function ($query) {
                            $query->where('enrollments.enrollable_type', Event::class)
                                ->whereColumn('certificates.event_id', 'enrollments.enrollable_id');
                        });
                });
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorshipMaterial extends Model
{
    protected $fillable = [
        'mentorship_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'created_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_by' => 'integer',
    ];

    public function mentorship()
    {
        return $this->belongsTo(Mentorship::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

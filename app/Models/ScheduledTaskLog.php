<?php
// UTF-8 sem BOM
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledTaskLog extends Model
{
    protected $table = 'scheduled_task_logs';
    protected $fillable = [
        'scheduled_task_id',
        'executed_at',
        'output',
        'success',
    ];
    protected $casts = [
        'executed_at' => 'datetime',
        'success' => 'boolean',
    ];
}

<?php
// UTF-8 sem BOM
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledTask extends Model
    public function logs()
    {
        return $this->hasMany(ScheduledTaskLog::class);
    }
{
    protected $table = 'scheduled_tasks';
    protected $fillable = [
        'command',
        'frequency', // ex: '* * * * *' (cron), ou presets: hourly, daily, etc.
        'active',
        'last_run_at',
    ];
    protected $casts = [
        'active' => 'boolean',
        'last_run_at' => 'datetime',
    ];
}

<?php
// UTF-8 sem BOM
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledTask extends Model
{
    private const DANGEROUS_PATTERNS = [
        '/(^|\\s)migrate:fresh(\\s|$)/i',
        '/(^|\\s)migrate:reset(\\s|$)/i',
        '/(^|\\s)migrate:rollback(\\s|$)/i',
        '/(^|\\s)db:wipe(\\s|$)/i',
        '/(^|\\s)db:drop(\\s|$)/i',
        '/(^|\\s)backup:restore(\\s|$)/i',
        '/(^|\\s)db:restore(\\s|$)/i',
        '/(^|\\s)database:restore(\\s|$)/i',
        '/(^|\\s)restore(\\s|$)/i',
    ];

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

    public function logs()
    {
        return $this->hasMany(ScheduledTaskLog::class);
    }

    public static function allowedCommands(): array
    {
        $groups = (array) config('cron-panel.commands', []);

        return collect($groups)
            ->flatMap(fn ($commands) => array_keys((array) $commands))
            ->values()
            ->all();
    }

    public static function isDangerousCommand(?string $command): bool
    {
        $normalized = trim((string) $command);
        if ($normalized === '') {
            return true;
        }

        foreach (self::DANGEROUS_PATTERNS as $pattern) {
            if (preg_match($pattern, $normalized) === 1) {
                return true;
            }
        }

        return false;
    }

    public static function isAllowedForScheduler(?string $command): bool
    {
        $normalized = trim((string) $command);

        return $normalized !== ''
            && !self::isDangerousCommand($normalized)
            && in_array($normalized, self::allowedCommands(), true);
    }

    public static function schedulerValidationRules(): array
    {
        return [
            'command' => ['required', 'string', 'in:' . implode(',', self::allowedCommands())],
            'frequency' => ['required', 'string'],
            'active' => ['boolean'],
        ];
    }
}

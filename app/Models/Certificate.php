<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\Content\SoldContentGuard;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'course_id', 'mentorship_id', 'event_id', 'cert_hash', 'pdf_path', 'issued_at', 'workload'];

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

    public function getContentTitleAttribute(): string
    {
        $product = $this->course ?? $this->mentorship ?? $this->event;
        $title = trim((string) ($product?->title ?? ''));
        if ($title !== '') {
            return $title;
        }

        [$type, $id] = match (true) {
            (int) ($this->course_id ?? 0) > 0 => ['course', (int) $this->course_id],
            (int) ($this->mentorship_id ?? 0) > 0 => ['mentorship', (int) $this->mentorship_id],
            (int) ($this->event_id ?? 0) > 0 => ['event', (int) $this->event_id],
            default => ['', 0],
        };

        return app(SoldContentGuard::class)->titleFromFinancialHistory(
            (int) $this->user_id,
            $type,
            $id
        ) ?: 'Conteúdo removido';
    }
}

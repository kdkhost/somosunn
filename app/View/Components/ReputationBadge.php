<?php

namespace App\View\Components;

use App\Services\ReputationService;
use Illuminate\View\Component;

class ReputationBadge extends Component
{
    public int $score;
    public string $icon;
    public string $color;
    public string $label;
    public string $size;
    public ?array $dimensions;

    public function __construct(int $score = 50, ?array $dimensions = null, string $size = 'sm')
    {
        $this->score = $score;
        $this->size = $size;
        $this->dimensions = $dimensions;

        $badge = ReputationService::getBadgeData($score);
        $this->icon = $badge['icon'];
        $this->color = $badge['color'];
        $this->label = $badge['label'];
    }

    /**
     * Retorna a classe CSS do icone FontAwesome baseado no identificador do badge.
     */
    public function iconClass(): string
    {
        return match ($this->icon) {
            'star' => 'fa-star',
            'shield' => 'fa-shield-alt',
            'circle' => 'fa-check-circle',
            'triangle' => 'fa-exclamation-triangle',
            'exclamation' => 'fa-exclamation-circle',
            default => 'fa-circle',
        };
    }

    /**
     * Retorna o texto do tooltip com breakdown das dimensoes (se disponivel).
     */
    public function tooltipText(): string
    {
        $base = "{$this->label} ({$this->score}/100)";

        if ($this->dimensions) {
            $parts = [];
            if (isset($this->dimensions['delivery_rate'])) {
                $parts[] = 'Entrega: ' . number_format($this->dimensions['delivery_rate'], 0) . '%';
            }
            if (isset($this->dimensions['relationship_score'])) {
                $parts[] = 'Relacionamento: ' . number_format($this->dimensions['relationship_score'], 0);
            }
            if (isset($this->dimensions['interaction_score'])) {
                $parts[] = 'Interacao: ' . number_format($this->dimensions['interaction_score'], 0);
            }
            if (isset($this->dimensions['engagement_score'])) {
                $parts[] = 'Engajamento: ' . number_format($this->dimensions['engagement_score'], 0);
            }
            if (!empty($parts)) {
                $base .= ' | ' . implode(', ', $parts);
            }
        }

        return $base;
    }

    public function render()
    {
        return view('components.reputation-badge');
    }
}

<?php

namespace App\Console\Commands;

use App\Models\PointsLog;
use App\Models\User;
use App\Services\PointsService;
use Illuminate\Console\Command;

class AwardTopRankingPoints extends Command
{
    protected $signature = 'points:award-top-ranking';

    protected $description = 'Premia os 10 usuários com mais pontos no ranking (executar semanalmente via agendador).';

    public function handle(PointsService $ps): int
    {
        $top10 = User::orderByDesc('points')->limit(10)->get();

        if ($top10->isEmpty()) {
            $this->info('Nenhum usuário encontrado.');
            return self::SUCCESS;
        }

        $awarded = 0;

        foreach ($top10 as $user) {
            // Evita premiar mais de uma vez nos últimos 7 dias
            $alreadyAwardedThisWeek = PointsLog::where('user_id', $user->id)
                ->where('action_key', 'top_10_ranking')
                ->where('created_at', '>=', now()->subDays(7))
                ->exists();

            if ($alreadyAwardedThisWeek) {
                $this->line("Usuário #{$user->id} ({$user->name}) já premiado nesta semana. Ignorado.");
                continue;
            }

            $result = $ps->award($user, 'top_10_ranking', ['position' => $top10->search(fn ($u) => $u->id === $user->id) + 1]);

            if ($result) {
                $awarded++;
                $this->info("Premiado: {$user->name} (#{$user->id}) — top_10_ranking");
            } else {
                $this->line("Regra top_10_ranking inativa ou não encontrada para usuário #{$user->id}.");
            }
        }

        $this->info("Total premiados: {$awarded}");

        return self::SUCCESS;
    }
}

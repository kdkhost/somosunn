<?php

namespace App\Console\Commands;

use App\Models\PointsLog;
use App\Models\User;
use App\Services\PointsService;
use Illuminate\Console\Command;

class AwardBirthdayBonus extends Command
{
    protected $signature = 'points:award-birthday-bonus';

    protected $description = 'Premia usuários que fazem aniversário hoje com o birthday_bonus (executar diariamente).';

    public function handle(PointsService $ps): int
    {
        $today = now();
        $month = $today->month;
        $day   = $today->day;

        // Busca usuários que nasceram no mesmo mês e dia (qualquer ano)
        $users = User::whereNotNull('birth_date')
            ->whereMonth('birth_date', $month)
            ->whereDay('birth_date', $day)
            ->get();

        if ($users->isEmpty()) {
            $this->info('Nenhum aniversariante hoje.');
            return self::SUCCESS;
        }

        $awarded = 0;

        foreach ($users as $user) {
            // Guard anual: premia somente uma vez por ano de calendário
            $alreadyAwardedThisYear = PointsLog::where('user_id', $user->id)
                ->where('action_key', 'birthday_bonus')
                ->whereYear('created_at', $today->year)
                ->exists();

            if ($alreadyAwardedThisYear) {
                $this->line("Usuário #{$user->id} ({$user->name}) já recebeu birthday_bonus em {$today->year}. Ignorado.");
                continue;
            }

            $result = $ps->award($user, 'birthday_bonus', [
                'birthday' => $user->birth_date?->format('d/m'),
                'year'     => $today->year,
            ]);

            if ($result) {
                $awarded++;
                $this->info("Premiado: {$user->name} (#{$user->id}) — birthday_bonus");
            } else {
                $this->line("Regra birthday_bonus inativa ou não encontrada para usuário #{$user->id}.");
            }
        }

        $this->info("Total premiados hoje: {$awarded}");

        return self::SUCCESS;
    }
}

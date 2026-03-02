<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PointsLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        // Garante que o usuário tenha um referral_code
        if (empty($user->referral_code)) {
            do {
                $code = 'UNN' . strtoupper(substr(md5($user->id . microtime()), 0, 7));
            } while (User::where('referral_code', $code)->exists());

            $user->referral_code = $code;
            $user->save();
        }

        $referralLink = route('register') . '?ref=' . $user->referral_code;

        // Todos os logs de pontos gerados por indicação deste usuário (para uso offline na view)
        $referralPointsLogs = PointsLog::where('user_id', $user->id)
            ->where('action_key', 'referral')
            ->latest()
            ->get();

        $totalReferralPoints = $referralPointsLogs->sum('points');

        // IDs de indicados que já geraram pontos (meta contém new_user_id)
        $convertedUserIds = $referralPointsLogs
            ->map(fn($l) => json_decode($l->meta ?? '{}', true)['new_user_id'] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Usuários que este usuário indicou, com plano
        $referredUsers = User::where('referred_by', $user->id)
            ->with('plan:id,name,price,is_free')
            ->select('id', 'name', 'email', 'photo', 'created_at', 'plan_id', 'plan_expires_at')
            ->latest()
            ->paginate(20);

        $totalReferred   = User::where('referred_by', $user->id)->count();
        $convertedCount  = count(array_unique($convertedUserIds));
        $pendingCount    = max(0, $totalReferred - $convertedCount);

        // Planos disponíveis para exibir nomes (mapa id→name)
        $plansMap = Plan::whereIn('id', $referredUsers->pluck('plan_id')->filter()->unique()->values())
            ->pluck('name', 'id');

        return view('panel.referral.index', compact(
            'user',
            'referralLink',
            'referredUsers',
            'referralPointsLogs',
            'convertedUserIds',
            'totalReferralPoints',
            'totalReferred',
            'convertedCount',
            'pendingCount',
            'plansMap'
        ));
    }
}

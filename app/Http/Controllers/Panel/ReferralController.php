<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
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

        // Usuários que este usuário indicou
        $referredUsers = User::where('referred_by', $user->id)
            ->select('id', 'name', 'email', 'photo', 'created_at', 'plan_id')
            ->latest()
            ->paginate(20);

        // Logs de pontos ganhos por indicação
        $referralPointsLogs = PointsLog::where('user_id', $user->id)
            ->where('action_key', 'referral')
            ->latest()
            ->take(50)
            ->get();

        $totalReferralPoints = $referralPointsLogs->sum('points');
        $totalReferred = User::where('referred_by', $user->id)->count();

        return view('panel.referral.index', compact(
            'user',
            'referralLink',
            'referredUsers',
            'referralPointsLogs',
            'totalReferralPoints',
            'totalReferred'
        ));
    }
}

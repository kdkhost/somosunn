<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Connection;
use App\Models\Post;
use App\Models\Setting;
use App\Services\MemberSuggestionService;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    public function socialFeed()
    {
        $posts = Post::with('user')->latest()->paginate(10);

        $blockedUserIds = Connection::where(function ($q) {
            $q->where('requester_id', Auth::id())->orWhere('requested_id', Auth::id());
        })->where('status', 'blocked')->pluck('requester_id', 'requested_id')->flatten()->unique()->toArray();

        $connectedUserIds = Connection::where('status', 'accepted')
            ->where(function ($q) {
                $q->where('requester_id', Auth::id())->orWhere('requested_id', Auth::id());
            })
            ->get()
            ->map(function ($connection) {
                return $connection->requester_id === Auth::id()
                    ? $connection->requested_id
                    : $connection->requester_id;
            })
            ->unique()
            ->values()
            ->toArray();

        $recommendedUsers = collect();
        if (Auth::check()) {
            $recommendedUsers = app(MemberSuggestionService::class)
                ->suggest(Auth::user(), $blockedUserIds, $connectedUserIds, 6);
        }

        $connectionMap = [];
        if (Auth::check() && $recommendedUsers->isNotEmpty()) {
            $authId = Auth::id();
            $recommendedIds = $recommendedUsers->pluck('id')->all();
            $connections = Connection::where(function ($q) use ($authId, $recommendedIds) {
                $q->where('requester_id', $authId)->whereIn('requested_id', $recommendedIds);
            })->orWhere(function ($q) use ($authId, $recommendedIds) {
                $q->where('requested_id', $authId)->whereIn('requester_id', $recommendedIds);
            })->get();

            foreach ($connections as $connection) {
                $otherId = $connection->requester_id === $authId
                    ? $connection->requested_id
                    : $connection->requester_id;
                $connectionMap[$otherId] = $connection;
            }
        }

        $adsEnabled = (string) Setting::get('ads_enabled', '0') === '1';
        $adsCode = (string) Setting::get('ads_code_html', '');

        return view('admin.community.feed', [
            'posts' => $posts,
            'recommendedUsers' => $recommendedUsers,
            'connectionMap' => $connectionMap,
            'adsEnabled' => $adsEnabled,
            'adsCode' => $adsCode,
        ]);
    }

    public function portal()
    {
        return view('site.portal', [
            'extends' => 'admin.layouts.app'
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MentorshipResource;
use App\Models\Mentorship;
use Illuminate\Http\Request;

class MentorshipApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min($perPage, 100));

        $mentorships = Mentorship::query()
            ->with('mentor')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return MentorshipResource::collection($mentorships);
    }

    public function show(Mentorship $mentorship)
    {
        $mentorship->load('mentor');

        return new MentorshipResource($mentorship);
    }
}

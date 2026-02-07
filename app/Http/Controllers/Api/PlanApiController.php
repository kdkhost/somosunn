<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min($perPage, 100));

        $plans = Plan::query()
            ->where('is_active', true)
            ->orderByDesc('highlight')
            ->orderByDesc('is_featured')
            ->orderBy('price', 'asc')
            ->paginate($perPage);

        return PlanResource::collection($plans);
    }

    public function show(Plan $plan)
    {
        if (!$plan->is_active) {
            abort(404);
        }

        return new PlanResource($plan);
    }
}

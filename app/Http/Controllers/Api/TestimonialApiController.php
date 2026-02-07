<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TestimonialResource;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min($perPage, 100));

        $testimonials = Testimonial::query()
            ->where('status', 'approved')
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return TestimonialResource::collection($testimonials);
    }
}

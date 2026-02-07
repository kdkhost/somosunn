<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseApiController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->query('status', 'published');
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min($perPage, 100));

        $query = Course::query();

        if ($status !== 'all') {
            $query->where(function ($q) {
                $q->where('status', 'published')
                    ->orWhere('published', true);
            });
        }

        $courses = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return CourseResource::collection($courses);
    }

    public function show(Course $course)
    {
        if ($course->status !== 'published' && !$course->published) {
            abort(404);
        }

        return new CourseResource($course);
    }
}

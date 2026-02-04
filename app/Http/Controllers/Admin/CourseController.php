<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::latest()->get();
        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('admin.courses.form', ['course' => new Course]);
    }

    public function store(Request $request)
    {
        if ($request->has('price')) {
            $price = $request->price;
            // Remove R$, whitespace, and dots (thousand separators)
            $price = str_replace(['R$', ' ', '.'], '', $price);
            // Replace comma with dot
            $price = str_replace(',', '.', $price);
            $request->merge(['price' => $price]);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'full_description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'author_name' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived,paused',
            'thumbnail' => 'nullable|image|max:10240', // 10MB Max
        ]);
        
        $data['is_featured'] = $request->has('is_featured');
        $data['is_certificate_enabled'] = $request->has('is_certificate_enabled');
        
        // Handle Certificate Settings
        if($request->has('certificate_settings')){
            $data['certificate_settings'] = json_decode($request->certificate_settings, true);
        }

        // Legacy support automation
        $data['published'] = ($data['status'] === 'published');
        $data['price'] = (!isset($data['price']) || $data['price'] === null || $data['price'] === '') ? 0 : $data['price'];
        
        // Validation for new fields
        $request->validate([
             'certificate_bg' => 'nullable|image|max:5120', // 5MB
             'certificate_settings' => 'nullable|json',
        ]);

        if ($request->hasFile('certificate_bg')) {
             $file = $request->file('certificate_bg');
             $fileName = 'cert_bg_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
             $file->move(public_path('uploads/certificates'), $fileName);
             $data['certificate_bg'] = 'uploads/certificates/' . $fileName;
        }

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/course-thumbs'), $fileName);
            $data['thumbnail'] = 'uploads/course-thumbs/' . $fileName;
        }
        
        Course::create($data + ['user_id' => auth()->id()]);
        
        return response()->json(['success' => true]); 
    }

    public function edit(Course $course)
    {
        return view('admin.courses.form', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        if ($request->has('price')) {
            $price = $request->price;
            $price = str_replace(['R$', ' ', '.'], '', $price);
            $price = str_replace(',', '.', $price);
            $request->merge(['price' => $price]);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'full_description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'author_name' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived,paused',
            'thumbnail' => 'nullable|image|max:10240', // 10MB Max
        ]);

        $data['is_featured'] = $request->has('is_featured');
        $data['is_certificate_enabled'] = $request->has('is_certificate_enabled');
        
        // Handle Certificate Settings
        if($request->has('certificate_settings')){
            // Depending on frontend, it might come as string JSON or array if not processed by JS.
            // Assuming string from hidden input
             $data['certificate_settings'] = json_decode($request->certificate_settings, true);
        }

        // Legacy support automation
        $data['published'] = ($data['status'] === 'published');
        $data['price'] = (!isset($data['price']) || $data['price'] === null || $data['price'] === '') ? 0 : $data['price'];

        // Validation for new fields
        $request->validate([
             'certificate_bg' => 'nullable|image|max:5120',
             'certificate_settings' => 'nullable|json',
        ]);

        if ($request->hasFile('certificate_bg')) {
             if($course->certificate_bg && file_exists(public_path($course->certificate_bg))){
                 @unlink(public_path($course->certificate_bg));
             }
             $file = $request->file('certificate_bg');
             $fileName = 'cert_bg_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
             $file->move(public_path('uploads/certificates'), $fileName);
             $data['certificate_bg'] = 'uploads/certificates/' . $fileName;
        }

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/course-thumbs'), $fileName);
            $data['thumbnail'] = 'uploads/course-thumbs/' . $fileName;
        }

        $course->update($data);
        
        return response()->json(['success' => true]);
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('admin.courses.index')->with('success','Curso removido');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mentorship;

class MentorshipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Pega mentorias disponíveis
        $mentorships = Mentorship::latest()->paginate(12);
        return view('mentorships.index', compact('mentorships'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Mentorship $mentorship)
    {
        return view('mentorships.show', compact('mentorship'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EventController extends Controller
{
    public function show(\App\Models\Event $event)
    {
        return view('site.events.show', compact('event'));
    }
}

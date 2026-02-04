<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return "<h1>DASHBOARD CONTROLLER REACHED</h1>If you see this, the Middleware is fine. The error is in the View/Blade.";
    }
}
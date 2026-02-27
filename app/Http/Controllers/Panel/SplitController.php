<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\OrderSplit;
use Illuminate\Http\Request;

class SplitController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $splits = OrderSplit::where('receiver_type', 'user')
            ->where('receiver_id', $user->id)
            ->with(['order'])
            ->latest()
            ->paginate(15);

        return view('panel.splits.index', compact('splits'));
    }
}

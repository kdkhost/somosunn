<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SponsorController extends Controller {
    public function store(Request $request, Child $child) {
        $user = Auth::user();
        // create sponsor record
        Sponsor::create([ 'user_id'=>$user->id, 'child_id'=>$child->id, 'anonymized'=>true ]);
        // mark child as chosen
        $child->update(['status'=>'chosen']);
        return back();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model {
    protected $fillable = ['user_id','child_id','anonymized','purchased','purchased_at','revealed_at'];

    public function user() { return $this->belongsTo(User::class); }
    public function child() { return $this->belongsTo(Child::class); }
}

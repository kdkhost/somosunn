<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Child extends Model {
    protected $fillable = ['event_id','responsible_id','name','birthdate','age','sizes','toys','phone','address','reference_point','photo','status'];

    protected $casts = ['sizes' => 'array'];

    public function event() { return $this->belongsTo(Event::class); }
    public function responsible() { return $this->belongsTo(User::class, 'responsible_id'); }
    public function sponsors() { return $this->hasMany(Sponsor::class); }
}

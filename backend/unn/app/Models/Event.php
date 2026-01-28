<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model {
    protected $fillable = ['title','date','start_time','end_time','address','lat','lng','map_link','description'];

    public function children() {
        return $this->hasMany(Child::class);
    }
}

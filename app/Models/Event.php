<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['title','speaker','description','start_at','end_at','location','address','latitude','longitude','price','capacity','published', 'color', 'all_day'];
}
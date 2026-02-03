<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','speaker','description','start_at','end_at','location','address','latitude','longitude',
        'price','capacity','published', 'color', 'all_day',
        'batch_1_price', 'batch_1_deadline',
        'batch_2_price', 'batch_2_deadline',
        'batch_3_price', 'batch_3_deadline'
    ];
}
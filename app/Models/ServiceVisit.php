<?php
// UTF-8 sem BOM
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceVisit extends Model
{
    protected $table = 'service_visits';
    protected $fillable = [
        'service_type', // curso, evento, palestra, mentoria, site
        'service_id',   // id do curso/evento/etc (ou null para site)
        'user_id',      // pode ser null para visitantes
        'visited_at',
    ];
    public $timestamps = false;
}

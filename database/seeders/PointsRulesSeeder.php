<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PointsRule;

class PointsRulesSeeder extends Seeder
{
    public function run()
    {
        $defaults = [
            ['key'=>'signup','label'=>'Cadastro','points'=>50,'active'=>true],
            ['key'=>'daily_login','label'=>'Login diário','points'=>1,'active'=>true],
            ['key'=>'publish','label'=>'Publicar conteúdo','points'=>10,'active'=>true],
            ['key'=>'comment','label'=>'Comentar','points'=>3,'active'=>true],
            ['key'=>'attend_event','label'=>'Participar de evento','points'=>20,'active'=>true],
            ['key'=>'complete_course','label'=>'Concluir curso','points'=>50,'active'=>true],
            ['key'=>'mentor','label'=>'Oferecer mentoria','points'=>30,'active'=>true],
        ];

        foreach($defaults as $d){ PointsRule::updateOrCreate(['key'=>$d['key']], $d); }
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mail_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('mail_templates','slug')) {
                $table->string('slug')->unique()->after('name');
            }
            if (!Schema::hasColumn('mail_templates','category')) {
                $table->string('category')->default('sistema')->after('slug');
            }
            if (!Schema::hasColumn('mail_templates','locale')) {
                $table->string('locale')->default('pt-BR')->after('category');
            }
        });
        // preencher slugs existentes
        $existing = DB::table('mail_templates')->get();
        foreach ($existing as $row) {
            if (empty($row->slug)) {
                $slug = strtolower(preg_replace('/[^a-z0-9]+/','-', iconv('UTF-8','ASCII//TRANSLIT',$row->name)));
                $slug = trim($slug,'-') ?: 'template-'.$row->id;
                DB::table('mail_templates')->where('id',$row->id)->update(['slug'=>$slug,'category'=>$row->category ?? 'sistema','locale'=>$row->locale ?? 'pt-BR']);
            }
        }
    }

    public function down()
    {
        Schema::table('mail_templates', function (Blueprint $table) {
            // keep columns (não removemos para evitar perda de dados)
        });
    }
};

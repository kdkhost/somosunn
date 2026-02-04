<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('custom_fonts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome amigável da fonte
            $table->enum('type', ['file', 'google_link']); // Tipo: arquivo ou link do Google Fonts
            $table->string('file_path')->nullable(); // Caminho do arquivo se type = 'file'
            $table->string('google_font_url')->nullable(); // URL do Google Fonts se type = 'google_link'
            $table->string('font_family'); // Nome da família da fonte (ex: 'Roboto', 'Open Sans')
            $table->boolean('is_active')->default(true);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_fonts');
    }
};

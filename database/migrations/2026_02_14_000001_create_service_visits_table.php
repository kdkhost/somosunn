<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('service_visits', function (Blueprint $table) {
            $table->id();
            $table->string('service_type', 32); // curso, evento, palestra, mentoria, site
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('visited_at')->useCurrent();
            $table->index(['service_type', 'service_id']);
        });
    }
    public function down()
    {
        Schema::dropIfExists('service_visits');
    }
};

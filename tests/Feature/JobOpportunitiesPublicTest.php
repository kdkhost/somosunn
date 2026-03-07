<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class JobOpportunitiesPublicTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-job-opportunities.sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_jobs_index_supports_filters_and_partner_highlight(): void
    {
        Partner::query()->create([
            'name' => 'Acme Labs',
            'slug' => 'acme-labs',
            'active' => true,
            'order' => 1,
        ]);

        JobVacancy::query()->create([
            'title' => 'Executivo de Parcerias',
            'company_name' => 'Acme Labs',
            'location' => 'São Paulo',
            'type' => 'Presencial',
            'short_description' => 'Negócios e relacionamento estratégico',
            'description' => '<p>Detalhes da vaga</p>',
            'visibility' => 'public',
            'is_active' => true,
        ]);

        JobVacancy::query()->create([
            'title' => 'Desenvolvedor Backend',
            'company_name' => 'Outra Empresa',
            'location' => 'Remoto',
            'type' => 'Remoto',
            'short_description' => 'PHP e APIs',
            'description' => '<p>Outra vaga</p>',
            'visibility' => 'public',
            'is_active' => true,
        ]);

        $response = $this->get(route('jobs.public.index', [
            'area' => 'Negócios',
            'local' => 'São Paulo',
            'empresa' => 'Acme',
            'tipo' => 'Presencial',
        ]));

        $response
            ->assertOk()
            ->assertSee('Executivo de Parcerias')
            ->assertSee('Empresa Parceira')
            ->assertSee('Limpar filtros')
            ->assertSee('Explorar Oportunidade')
            ->assertDontSee('Desenvolvedor Backend');
    }

    public function test_job_show_displays_quick_apply_and_application_status(): void
    {
        $user = User::query()->create([
            'name' => 'Candidato',
            'email' => 'candidato@example.com',
            'password' => bcrypt('secret'),
            'role' => 'member',
        ]);

        $job = JobVacancy::query()->create([
            'title' => 'Analista de Comunidade',
            'company_name' => 'Comunidade UNN',
            'location' => 'Rio de Janeiro',
            'type' => 'Híbrido',
            'short_description' => 'Atuação com networking e comunidade',
            'description' => '<p>Descrição completa</p>',
            'visibility' => 'public',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('jobs.public.show', ['job' => $job->id]))
            ->assertOk()
            ->assertSee('Enviar Curriculo');

        JobApplication::query()->create([
            'job_vacancy_id' => $job->id,
            'user_id' => $user->id,
            'resume_path' => 'curriculos/candidato.pdf',
            'status' => 'reviewing',
        ]);

        $this->actingAs($user)
            ->get(route('jobs.public.show', ['job' => $job->id]))
            ->assertOk()
            ->assertSee('Standby')
            ->assertSee('Ver andamento no painel');
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->string('level')->nullable();
            $table->string('photo')->nullable();
            $table->string('referral_code', 20)->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->string('company_name')->nullable();
            $table->string('location')->nullable();
            $table->string('type')->nullable();
            $table->string('level')->nullable();
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->text('benefits')->nullable();
            $table->string('salary_range')->nullable();
            $table->string('image')->nullable();
            $table->string('visibility')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_demo')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('job_applies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_vacancy_id');
            $table->unsignedBigInteger('user_id');
            $table->string('resume_path')->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('logo')->nullable();
            $table->string('website_url')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('partner_coupons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_id');
            $table->string('code')->nullable();
            $table->boolean('active')->default(true);
            $table->date('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title')->nullable();
            $table->text('data')->nullable();
            $table->timestamps();
        });
    }
}

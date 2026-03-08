<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MailTemplate;

class JobApplicationMailTemplatesSeeder extends Seeder
{
  public function run(): void
  {
    // Template para o CANDIDATO: confirmação de candidatura
    MailTemplate::firstOrCreate(
      ['slug' => 'job_apply_candidate'],
      [
        'name' => 'Confirmação de Candidatura (Candidato)',
        'category' => 'vagas',
        'locale' => 'pt-BR',
        'subject' => 'Sua candidatura para "{vacancy_title}" foi recebida!',
        'is_active' => true,
        'body' => <<<HTML
<p>Olá, <strong>{name}</strong>!</p>
<p>Recebemos sua candidatura para a vaga abaixo. O responsável pela vaga irá analisar seu currículo e entrará em contato em breve.</p>
<div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:12px;padding:20px 24px;margin:0 0 24px;">
    <p style="margin:0 0 6px;font-size:12px;color:#0ea5e9;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Detalhes da Vaga</p>
    <p style="margin:0;font-size:18px;font-weight:700;color:#1e40af;">{vacancy_title}</p>
    <p style="margin:4px 0 0;font-size:14px;color:#64748b;"><strong>Empresa:</strong> {company} &nbsp;|&nbsp; <strong>Local:</strong> {location}</p>
</div>
<p style="text-align:center;">Acompanhe suas candidaturas pelo painel <a href="{site_url}">{site_name}</a></p>
HTML,
      ]
    );

    // Template para o DONO da vaga: nova candidatura recebida
    MailTemplate::firstOrCreate(
      ['slug' => 'job_apply_owner'],
      [
        'name' => 'Nova Candidatura Recebida (Dono da Vaga)',
        'category' => 'vagas',
        'locale' => 'pt-BR',
        'subject' => 'Nova candidatura para "{vacancy_title}"',
        'is_active' => true,
        'body' => <<<HTML
<p>Olá, <strong>{owner_name}</strong>!</p>
<p><strong>{candidate}</strong> acabou de se candidatar para a sua vaga <strong>{vacancy_title}</strong>.</p>
<div style="text-align:center;margin:24px 0;">
    <a href="{candidates_url}" style="display:inline-block;background:#7c3aed;color:#fff;font-size:15px;font-weight:700;padding:14px 32px;border-radius:10px;text-decoration:none;">Ver Candidatos</a>
</div>
<p style="text-align:center;">Acesse o painel <a href="{site_url}">{site_name}</a> para gerenciar os candidatos</p>
HTML,
      ]
    );

    $this->command->info('✅ 2 mail templates de candidatura criados (job_apply_candidate, job_apply_owner).');
  }
}

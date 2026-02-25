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
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Candidatura Recebida</title></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
    <tr><td align="center">
      <table width="580" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">
        <tr><td style="background:linear-gradient(135deg,#2563eb,#1d4ed8);padding:40px 40px 32px;text-align:center;">
          <h1 style="margin:0;color:#fff;font-size:26px;font-weight:700;">✅ Candidatura Enviada!</h1>
          <p style="margin:8px 0 0;color:rgba(255,255,255,.85);font-size:14px;">Sua candidatura foi recebida com sucesso</p>
        </td></tr>
        <tr><td style="padding:40px;">
          <p style="margin:0 0 16px;font-size:16px;color:#374151;">Olá, <strong>{name}</strong>!</p>
          <p style="margin:0 0 24px;font-size:15px;color:#6b7280;line-height:1.7;">Recebemos sua candidatura para a vaga abaixo. O responsável pela vaga irá analisar seu currículo e entrará em contato em breve.</p>
          <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:12px;padding:20px 24px;margin:0 0 24px;">
            <p style="margin:0 0 6px;font-size:12px;color:#0ea5e9;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Detalhes da Vaga</p>
            <p style="margin:0;font-size:18px;font-weight:700;color:#1e40af;">{vacancy_title}</p>
            <p style="margin:4px 0 0;font-size:14px;color:#64748b;"><strong>Empresa:</strong> {company} &nbsp;|&nbsp; <strong>Local:</strong> {location}</p>
          </div>
          <p style="margin:0;font-size:14px;color:#9ca3af;text-align:center;">Acompanhe suas candidaturas pelo painel <a href="{site_url}" style="color:#2563eb;">{site_name}</a></p>
        </td></tr>
        <tr><td style="background:#f8fafc;padding:20px 40px;text-align:center;border-top:1px solid #f1f5f9;">
          <p style="margin:0;font-size:12px;color:#9ca3af;">© {site_name} · Todos os direitos reservados</p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
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
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Nova Candidatura</title></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
    <tr><td align="center">
      <table width="580" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">
        <tr><td style="background:linear-gradient(135deg,#7c3aed,#6d28d9);padding:40px 40px 32px;text-align:center;">
          <h1 style="margin:0;color:#fff;font-size:26px;font-weight:700;">👤 Nova Candidatura!</h1>
          <p style="margin:8px 0 0;color:rgba(255,255,255,.85);font-size:14px;">Um candidato se inscreveu na sua vaga</p>
        </td></tr>
        <tr><td style="padding:40px;">
          <p style="margin:0 0 16px;font-size:16px;color:#374151;">Olá, <strong>{owner_name}</strong>!</p>
          <p style="margin:0 0 24px;font-size:15px;color:#6b7280;line-height:1.7;"><strong>{candidate}</strong> acabou de se candidatar para a sua vaga <strong>{vacancy_title}</strong>.</p>
          <div style="text-align:center;margin:24px 0;">
            <a href="{candidates_url}" style="display:inline-block;background:#7c3aed;color:#fff;font-size:15px;font-weight:700;padding:14px 32px;border-radius:10px;text-decoration:none;">Ver Candidatos</a>
          </div>
          <p style="margin:0;font-size:14px;color:#9ca3af;text-align:center;">Acesse o painel <a href="{site_url}" style="color:#7c3aed;">{site_name}</a> para gerenciar os candidatos</p>
        </td></tr>
        <tr><td style="background:#f8fafc;padding:20px 40px;text-align:center;border-top:1px solid #f1f5f9;">
          <p style="margin:0;font-size:12px;color:#9ca3af;">© {site_name} · Todos os direitos reservados</p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML,
            ]
        );

        $this->command->info('✅ 2 mail templates de candidatura criados (job_apply_candidate, job_apply_owner).');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        Log::info('Contato: formulario recebido', [
            'ip' => $request->ip(),
            'has_token' => $request->filled('recaptcha_token'),
        ]);

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190',
            'phone' => 'nullable|string|max:40',
            'subject' => 'required|string|max:40',
            'message' => 'required|string|min:10|max:4000',
            'recaptcha_token' => 'nullable|string|max:5000',
        ]);

        Log::info('Contato: validacao passou');

        if (!$this->verifyRecaptchaV3($request)) {
            Log::warning('Contato: reCAPTCHA falhou');
            return back()
                ->withInput()
                ->with('error', 'Falha na verificacao de seguranca (reCAPTCHA). Tente novamente.');
        }

        Log::info('Contato: reCAPTCHA OK');

        $this->applySmtpSettingsFromDatabase();

        $to = trim((string) Setting::get('company_email', ''));
        if ($to === '') {
            $to = trim((string) Setting::get('smtp_from_email', ''));
        }
        if ($to === '') {
            $to = trim((string) config('mail.from.address', ''));
        }

        if ($to === '') {
            Log::warning('Contato: email de destino nao configurado (company_email/smtp_from_email/mail.from.address).');
            return back()
                ->withInput()
                ->with('error', 'Email de contato nao configurado. Tente novamente mais tarde.');
        }

        $subjectLabels = [
            'duvidas' => 'Duvidas sobre a plataforma',
            'parcerias' => 'Propostas de parceria',
            'suporte' => 'Suporte tecnico',
            'comercial' => 'Departamento comercial',
            'imprensa' => 'Assessoria de imprensa',
            'outro' => 'Outro assunto',
        ];

        $siteName = Setting::get('app_name') ?: config('app.name', 'UNN');
        $subjectText = $subjectLabels[$data['subject']] ?? $data['subject'];
        $mailSubject = "[Contato] {$subjectText} - {$siteName}";

        $html = view('emails.contact', [
            'data' => [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'subject' => $subjectText,
                'message' => $data['message'],
                'ip' => $request->ip(),
                'userAgent' => (string) $request->userAgent(),
            ],
        ])->render();

        try {
            Mail::html($html, function ($message) use ($to, $mailSubject, $data) {
                $message->to($to)->subject($mailSubject);
                $message->replyTo($data['email'], $data['name']);
            });
        } catch (\Throwable $e) {
            Log::error('Contato: falha ao enviar email: ' . $e->getMessage(), [
                'to' => $to,
                'smtp_host' => config('mail.mailers.smtp.host'),
                'smtp_port' => config('mail.mailers.smtp.port'),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Nao foi possivel enviar sua mensagem agora. Tente novamente mais tarde.');
        }

        return back()->with('success', 'Mensagem enviada com sucesso! Em breve retornaremos seu contato.');
    }

    private function verifyRecaptchaV3(Request $request): bool
    {
        $siteKey = trim((string) (Setting::get('recaptcha_v3_site_key') ?: config('services.recaptcha.site_key', '')));
        $secret = trim((string) (Setting::get('recaptcha_v3_secret_key') ?: config('services.recaptcha.v3_secret', '')));

        Log::info('Contato: verificando reCAPTCHA', [
            'has_site_key' => $siteKey !== '',
            'has_secret' => $secret !== '',
        ]);

        if ($siteKey === '' || $secret === '') {
            Log::info('Contato: reCAPTCHA nao configurado, permitindo');
            return true;
        }

        $token = trim((string) $request->input('recaptcha_token', ''));
        if ($token === '') {
            // Token vazio = reCAPTCHA falhou no cliente (bloqueador, timeout, etc)
            // Permitir envio mas logar para monitoramento
            Log::warning('Contato: token vazio (reCAPTCHA pode ter falhado no cliente), permitindo com aviso');
            return true;
        }

        $minScoreRaw = (string) (Setting::get('recaptcha_v3_min_score') ?: config('services.recaptcha.v3_min_score', 0.5));
        $minScore = (float) str_replace(',', '.', $minScoreRaw);
        $minScore = max(0.0, min(1.0, $minScore));

        try {
            $response = Http::asForm()
                ->timeout(8)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);

            if (!$response->ok()) {
                Log::warning('reCAPTCHA: resposta nao OK', ['status' => $response->status()]);
                return false;
            }

            $payload = $response->json();
            if (!is_array($payload) || !($payload['success'] ?? false)) {
                Log::info('reCAPTCHA: validacao falhou', ['payload' => $payload]);
                return false;
            }

            $action = (string) ($payload['action'] ?? '');
            if ($action !== '' && $action !== 'contact') {
                Log::info('reCAPTCHA: action inesperada', ['action' => $action]);
                return false;
            }

            $score = (float) ($payload['score'] ?? 0);
            if ($score < $minScore) {
                Log::info('reCAPTCHA: score abaixo do minimo', ['score' => $score, 'min' => $minScore]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA: excecao ao validar', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function applySmtpSettingsFromDatabase(): void
    {
        try {
            $host = trim((string) Setting::get('smtp_host', ''));
            $port = trim((string) Setting::get('smtp_port', ''));
            $username = trim((string) Setting::get('smtp_username', ''));
            $password = (string) Setting::get('smtp_password', '');
            $encryption = trim((string) Setting::get('smtp_encryption', ''));
            $fromEmail = trim((string) Setting::get('smtp_from_email', ''));
            $fromName = trim((string) Setting::get('smtp_from_name', ''));

            if ($host === '' || $port === '' || $fromEmail === '') {
                return;
            }

            if ($encryption === 'null') {
                $encryption = '';
            }

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => $port,
                'mail.mailers.smtp.username' => $username !== '' ? $username : null,
                'mail.mailers.smtp.password' => $password !== '' ? $password : null,
                'mail.mailers.smtp.encryption' => $encryption !== '' ? $encryption : null,
                'mail.from.address' => $fromEmail,
                'mail.from.name' => $fromName !== '' ? $fromName : (Setting::get('app_name') ?: config('app.name', 'UNN')),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Contato: falha ao aplicar SMTP do banco', ['error' => $e->getMessage()]);
        }
    }
}

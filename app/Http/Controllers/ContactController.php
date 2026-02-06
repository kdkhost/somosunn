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
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190',
            'phone' => 'nullable|string|max:40',
            'subject' => 'required|string|max:40',
            'message' => 'required|string|min:10|max:4000',
            'recaptcha_token' => 'nullable|string|max:5000',
        ]);

        if (!$this->verifyRecaptchaV3($request)) {
            return back()
                ->withInput()
                ->with('error', 'Falha na verificação de segurança (reCAPTCHA). Tente novamente.');
        }

        $this->applySmtpSettingsFromDatabase();

        $to = trim((string) Setting::get('company_email', ''));
        if ($to === '') {
            $to = trim((string) config('mail.from.address', ''));
        }

        if ($to === '') {
            Log::warning('Contato: e-mail de destino não configurado (company_email / mail.from.address).');
            return back()
                ->withInput()
                ->with('error', 'E-mail de contato não configurado. Tente novamente mais tarde.');
        }

        $subjectLabels = [
            'duvidas' => 'Dúvidas sobre a plataforma',
            'parcerias' => 'Propostas de parceria',
            'suporte' => 'Suporte técnico',
            'comercial' => 'Departamento comercial',
            'imprensa' => 'Assessoria de imprensa',
            'outro' => 'Outro assunto',
        ];

        $siteName = Setting::get('app_name') ?: config('app.name', 'UNN');
        $subjectText = $subjectLabels[$data['subject']] ?? $data['subject'];
        $mailSubject = "[Contato] {$subjectText} — {$siteName}";

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
            Log::error('Contato: falha ao enviar e-mail: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Não foi possível enviar sua mensagem agora. Tente novamente mais tarde.');
        }

        return back()->with('success', 'Mensagem enviada com sucesso! Em breve retornaremos seu contato.');
    }

    private function verifyRecaptchaV3(Request $request): bool
    {
        $secret = (string) config('services.recaptcha.v3_secret', '');
        if ($secret === '') {
            // Sem secret configurado, não bloqueia o formulário (ambiente dev/staging).
            return true;
        }

        $token = trim((string) $request->input('recaptcha_token', ''));
        if ($token === '') {
            return false;
        }

        $minScore = (float) config('services.recaptcha.v3_min_score', 0.5);

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);

            if (!$response->ok()) {
                Log::warning('reCAPTCHA: resposta não OK: ' . $response->status());
                return false;
            }

            $payload = $response->json();
            if (!is_array($payload) || !($payload['success'] ?? false)) {
                Log::info('reCAPTCHA: falhou', ['payload' => $payload]);
                return false;
            }

            $action = (string) ($payload['action'] ?? '');
            if ($action !== '' && $action !== 'contact') {
                Log::info('reCAPTCHA: action inesperada', ['action' => $action]);
                return false;
            }

            $score = (float) ($payload['score'] ?? 0);
            if ($score < $minScore) {
                Log::info('reCAPTCHA: score abaixo do mínimo', ['score' => $score, 'min' => $minScore]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA: exceção ao validar: ' . $e->getMessage());
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
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => $port,
                'mail.mailers.smtp.username' => $username !== '' ? $username : null,
                'mail.mailers.smtp.password' => $password !== '' ? $password : null,
                'mail.mailers.smtp.encryption' => $encryption !== '' ? $encryption : null,
                'mail.from.address' => $fromEmail,
                'mail.from.name' => $fromName !== '' ? $fromName : (Setting::get('app_name') ?: config('app.name')),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Contato: falha ao aplicar SMTP do banco: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MailTestController extends Controller
{
    public function showForm()
    {
        return view('admin.mail_test');
    }

    public function sendTest(Request $request)
    {
        $request->validate(['to' => 'required|email','subject' => 'required','body' => 'required']);
        \Log::info('Mail test requested', $request->all());

        if (class_exists('\\PHPMailer\\PHPMailer\\PHPMailer') || class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
            try {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = env('MAIL_HOST');
                $mail->SMTPAuth = true;
                $mail->Username = env('MAIL_USERNAME');
                $mail->Password = env('MAIL_PASSWORD');
                $mail->SMTPSecure = env('MAIL_ENCRYPTION') ?: '';
                $mail->Port = env('MAIL_PORT');

                $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                $mail->addAddress($request->input('to'));

                $mail->isHTML(true);
                $mail->Subject = $request->input('subject');
                $mail->Body = $request->input('body');

                $mail->send();
                return redirect()->route('admin.mailtest')->with('success', 'E-mail enviado com sucesso.');
            } catch (\Exception $e) {
                \Log::error('PHPMailer error: '.$e->getMessage());
                return redirect()->route('admin.mailtest')->with('error', 'Erro ao enviar: '.$e->getMessage());
            }
        }

        return redirect()->route('admin.mailtest')->with('error', 'PHPMailer não instalado. Instale `phpmailer/phpmailer`.');
    }

    public function sendRaw($to, $subject, $html)
    {
        if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
            try {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = env('MAIL_HOST');
                $mail->SMTPAuth = true;
                $mail->Username = env('MAIL_USERNAME');
                $mail->Password = env('MAIL_PASSWORD');
                $mail->SMTPSecure = env('MAIL_ENCRYPTION') ?: null;
                $mail->Port = env('MAIL_PORT');

                $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                $mail->addAddress($to);

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $html;

                $mail->send();
                return response()->json(['message' => 'Enviado']);
            } catch (\Exception $e) {
                \Log::error('PHPMailer error: '.$e->getMessage());
                return response()->json(['error' => 'Erro ao enviar: '.$e->getMessage()], 500);
            }
        }
        return response()->json(['error' => 'PHPMailer não instalado.'], 500);
    }
}

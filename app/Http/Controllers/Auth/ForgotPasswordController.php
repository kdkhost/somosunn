<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Exibe o formulário de solicitação de reset de senha.
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Envia o link de redefinição de senha por email.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Informe um e-mail válido.',
        ]);

        // Envia o link de reset
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Enviamos o link de redefinição para seu e-mail!');
        }

        // Traduz os erros
        $messages = [
            Password::INVALID_USER => 'Não encontramos um usuário com esse e-mail.',
            Password::RESET_THROTTLED => 'Aguarde alguns minutos antes de solicitar novamente.',
        ];

        return back()->withErrors([
            'email' => $messages[$status] ?? 'Erro ao enviar o link de redefinição.',
        ])->withInput();
    }
}

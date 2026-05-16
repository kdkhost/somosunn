<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /**
     * Exibe o formulário de redefinição de senha.
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Processa a redefinição de senha.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ], [
            'token.required' => 'Token inválido.',
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'password.required' => 'Informe a nova senha.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'As senhas não conferem.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            // Audit log: alteração de senha bem-sucedida
            try {
                app(AuditLogService::class)->log(
                    AuditLogService::ACTION_PASSWORD_CHANGE,
                    null,
                    [],
                    [],
                    ['email' => $request->email]
                );
            } catch (\Throwable $e) { /* silent: audit nunca quebra reset */ }

            return redirect()->route('login')->with('status', 'Senha redefinida com sucesso! Faça login com sua nova senha.');
        }

        // Traduz os erros
        $messages = [
            Password::INVALID_USER => 'Não encontramos um usuário com esse e-mail.',
            Password::INVALID_TOKEN => 'Este link de redefinição expirou ou é inválido.',
            Password::RESET_THROTTLED => 'Aguarde alguns minutos antes de tentar novamente.',
        ];

        return back()->withErrors([
            'email' => $messages[$status] ?? 'Erro ao redefinir a senha.',
        ])->withInput(['email' => $request->email]);
    }
}

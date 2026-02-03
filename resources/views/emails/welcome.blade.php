<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; background-color: #f8fbff; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #1F5EDB 0%, #177FD6 100%); padding: 30px; text-align: center; color: white; }
        .content { padding: 30px; line-height: 1.6; color: #334155; }
        .button { display: inline-block; background-color: #1F5EDB; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
        .footer { background-color: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bem-vindo à UNN! 🚀</h1>
        </div>
        <div class="content">
            <p>Olá, <strong>{{ $user->name }}</strong>!</p>
            <p>Estamos muito felizes em ter você conosco. Sua conta foi criada com sucesso e agora você faz parte da maior comunidade de networking estratégico.</p>
            <p>Aproveite para conectar-se com outros empreendedores, acessar conteúdos exclusivos e expandir seus negócios.</p>
            <center>
                <a href="{{ route('login') }}" class="button">Acessar minha conta</a>
            </center>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} UNN - Todos os direitos reservados.
        </div>
    </div>
</body>
</html>

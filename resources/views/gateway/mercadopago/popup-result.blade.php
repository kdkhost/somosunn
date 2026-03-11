<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $success ? 'Conexao concluida' : 'Falha na conexao' }}</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top, #1d4ed8, #020617 62%);
            color: #e2e8f0;
            font-family: "Segoe UI", sans-serif;
        }

        .card {
            width: min(30rem, calc(100vw - 2rem));
            padding: 2rem;
            border-radius: 1.75rem;
            background: rgba(15, 23, 42, 0.92);
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.4);
            text-align: center;
        }

        .icon {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1rem;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            background: {{ $success ? 'rgba(16, 185, 129, 0.16)' : 'rgba(248, 113, 113, 0.16)' }};
            color: {{ $success ? '#6ee7b7' : '#fca5a5' }};
        }

        h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 800;
        }

        p {
            margin: 0.9rem 0 0;
            line-height: 1.7;
            color: #cbd5e1;
        }

        .hint {
            margin-top: 1.1rem;
            font-size: 0.82rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">{{ $success ? 'OK' : '!' }}</div>
        <h1>{{ $success ? 'Conexao concluida' : 'Nao foi possivel concluir' }}</h1>
        <p>{{ $message }}</p>
        <p class="hint">Esta janela sera fechada automaticamente.</p>
    </div>

    <script>
        (function () {
            const payload = {
                type: 'mercadopago-oauth-result',
                success: @json($success),
                message: @json($message),
                payload: @json($payload),
                redirectUrl: @json($redirectUrl),
            };

            if (window.opener && !window.opener.closed) {
                window.opener.postMessage(payload, window.location.origin);
                window.setTimeout(function () {
                    window.close();
                }, 700);
                return;
            }

            window.location.replace(payload.redirectUrl);
        })();
    </script>
</body>
</html>

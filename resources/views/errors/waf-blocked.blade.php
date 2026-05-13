<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Acesso Bloqueado - {{ config('app.name', 'UNN') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #e2e8f0;
        }
        .container {
            max-width: 520px;
            width: 100%;
            text-align: center;
        }
        .shield-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #1F5EDB 0%, #177FD6 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 40px rgba(31, 94, 219, 0.3);
        }
        .shield-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }
        h1 {
            font-size: 1.8rem;
            font-weight: 800;
            color: #f8fafc;
            margin-bottom: 12px;
            letter-spacing: -0.02em;
        }
        .subtitle {
            font-size: 0.95rem;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .info-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            backdrop-filter: blur(10px);
        }
        .info-card p {
            font-size: 0.85rem;
            color: #cbd5e1;
            margin-bottom: 12px;
        }
        .ref-code {
            display: inline-block;
            background: rgba(31, 94, 219, 0.15);
            border: 1px solid rgba(31, 94, 219, 0.3);
            color: #93c5fd;
            padding: 8px 16px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #1F5EDB 0%, #177FD6 100%);
            color: white;
            box-shadow: 0 8px 20px rgba(31, 94, 219, 0.3);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(31, 94, 219, 0.4);
        }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #e2e8f0;
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.12);
        }
        .footer {
            margin-top: 40px;
            font-size: 0.75rem;
            color: #64748b;
        }
        .footer a { color: #94a3b8; text-decoration: none; }
        .footer a:hover { color: #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="shield-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
            </svg>
        </div>

        <h1>Acesso Bloqueado</h1>
        <p class="subtitle">
            Sua requisição foi identificada como potencialmente maliciosa pelo nosso sistema de segurança e foi bloqueada automaticamente.
        </p>

        <div class="info-card">
            <p>Se você acredita que isso é um engano, entre em contato com o suporte informando o código de referência abaixo:</p>
            <span class="ref-code">{{ $ref ?? 'N/A' }}</span>
        </div>

        <div class="actions">
            <a href="{{ url('/') }}" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                Voltar ao Início
            </a>
            <a href="javascript:history.back()" class="btn btn-secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                Página Anterior
            </a>
        </div>

        <div class="footer">
            <p>Protegido por {{ config('app.name', 'UNN') }} Firewall (WAF)</p>
        </div>
    </div>
</body>
</html>

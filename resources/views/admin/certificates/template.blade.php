<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificado</title>
    <style>body{font-family:Arial,Helvetica,sans-serif;text-align:center;padding:50px} .title{font-size:28px;font-weight:bold;} .sub{margin-top:20px}</style>
</head>
<body>
    <div class="title">Certificado de Participação</div>
    <div class="sub">Concedido a <strong>{{ $user->name }}</strong></div>
    <div class="sub">Pelo curso: <strong>{{ $course->title }}</strong></div>
    <div class="sub">Emitido em: {{ now()->format('d/m/Y') }}</div>
    <div class="sub">Código de validação: <strong>{{ $certHash }}</strong></div>
</body>
</html>
<?php

/*
|--------------------------------------------------------------------------
| Arquivo Legado de Roteamento Web
|--------------------------------------------------------------------------
| As rotas HTTP foram desmembradas por subdominio e responsabilidade:
| - routes/public.php   -> somosunn.com.br (paginas publicas cacheaveis GET/HEAD)
| - routes/app.php      -> app.somosunn.com.br (/admin, /painel, auth e checkout)
| - routes/webhooks.php -> api.somosunn.com.br (callbacks assincronos de gateway)
|
| Registrado no RouteServiceProvider mantendo 100% de retrocompatibilidade.
*/

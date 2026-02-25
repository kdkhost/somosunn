<?php

/*
|--------------------------------------------------------------------------
| Load The Cached Routes
|--------------------------------------------------------------------------
|
| Here we will decode and unserialize the RouteCollection instance that
| holds all of the route information for an application. This allows
| us to instantaneously load the entire route map into the router.
|
*/

app('router')->setCompiledRoutes(
    array (
  'compiled' => 
  array (
    0 => false,
    1 => 
    array (
      '/sanctum/csrf-cookie' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sanctum.csrf-cookie',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/health' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::uZ4h0jBV8Z5Ewff2',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/auth/register' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::d9kkKZ8PBO8QADed',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/auth/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::DuFDBQJVwqbK9xZQ',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/events' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::H0kaNXJEFQ8ignHE',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/courses' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::V87ikNzycejHuLYq',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/mentorships' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::kTK1DiZSHiOOIu5F',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/plans' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::WOCIUDbCozvkQpou',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/testimonials' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::b2sFoBJ0g4DjJgdf',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/auth/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::mXpYhg94o2v9mv49',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/me' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::c2mhfrc0Amp4GvSg',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/webhooks/mercadopago' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.webhooks.mercadopago',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/webhooks/pagseguro' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.webhooks.pagseguro',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/cron' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.cron.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.cron.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/cron/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.cron.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'home',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/portal' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'portal',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/premium' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'premium',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/depoimentos' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'testimonials.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/debug-test' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Y940t9NL8hdAfvcS',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/limpar-cache' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::fpeuJY6iQScC2pVn',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sobre' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sobre',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/manifesto' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'manifesto',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/quem-somos' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'quem-somos',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/como-funciona' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'como-funciona',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/valores' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'valores',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/contato' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'contato',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'contato.send',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/membros' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'membros',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/vagas-abertas' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'jobs.public.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/eventos' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'events.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'events.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/service-worker.js' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::SglQ3oCdPfhjXBiK',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/favicon.ico' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::rMD05X7poA6x18Mc',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'login',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ZYKaKLJCdxH0JLqP',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'logout',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/register' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'register',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::HCVBQGM5bv9JFyV3',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/password/forgot' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.request',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/password/email' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.email',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/password/reset' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.update',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/upload' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'upload.file',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/webhook/mercadopago' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::m1hyXhqJodt56aly',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/webhook/pagseguro' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ui613NBrPxkPVaNk',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/install' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'install.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/install/run' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'install.run',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::PPm5nZAtmDJonQVD',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/install/test-connection' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'install.test-connection',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/backend/install' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'install.index.legacy',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/backend/install/run' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'install.run.legacy',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::4GqEnr2fh4oTPha2',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/backend/install/test-connection' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'install.test-connection.legacy',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/interactions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.interactions.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/satisfactions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.satisfactions.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/ranking' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.ranking.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/manifest.webmanifest' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'manifest',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/offline' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'offline',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sitemap.xml' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sitemap',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/robots.txt' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::i08xFzNSVgyDnWSg',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/courses' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'courses.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/mentorships' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mentorships.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'mentorships.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/gateway/mercadopago/connect' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'gateway.mercadopago.connect',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/gateway/mercadopago/callback' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'gateway.mercadopago.callback',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/feed' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'social.feed',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/connection/notifications' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'connection.notifications',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/post' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'social.post.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/chat' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'chat.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/chat/list' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'chat.list',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/notifications/hub' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'notifications.hub',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/notificacoes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'notifications.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.dashboard',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/dashboard/stats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.dashboard.stats',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.dashboard',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/mailtemplates' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.mailtemplates.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.mailtemplates.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/mailtemplates/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.mailtemplates.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.users.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.users.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/users/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.users.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/plans' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.plans.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.plans.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/plans/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.plans.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/orders' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.orders.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/invoices' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.invoices.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.invoices.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/invoices/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.invoices.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/coupons' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.coupons.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.coupons.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/coupons/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.coupons.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/courses' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.courses.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.courses.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/courses/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.courses.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/mentorships' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.mentorships.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.mentorships.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/mentorships/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.mentorships.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/events' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.events.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.events.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/events/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.events.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/certificates' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.certificates.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.certificates.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/certificates/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.certificates.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/jobs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.jobs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.jobs.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/jobs/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.jobs.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/redemptions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.redemptions.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.redemptions.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/redemptions/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.redemptions.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/faqs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.faqs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.faqs.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/faqs/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.faqs.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/testimonials' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.testimonials.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.testimonials.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/testimonials/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.testimonials.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/cms' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.cms.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/logs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.logs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/logs/clear' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.logs.clear',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/points-rules' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.points-rules.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.points-rules.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/points-rules/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.points-rules.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/admin/ranking' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.ranking.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/theme/toggle' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.theme.toggle',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/perfil' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.profile.edit',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.profile.update',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/certificados' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.certificates.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/certificados/gerar' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.certificates.generate',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/marketplace' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.marketplace.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/marketplace/pagamentos' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.marketplace.payments',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/marketplace/pagamentos/configurar' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.marketplace.payments.edit',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/marketplace/pagamentos/testar' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.marketplace.payments.test',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/marketplace/vendas' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.marketplace.sales',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/vagas' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.jobs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/my-jobs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.my-jobs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.my-jobs.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/my-jobs/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.my-jobs.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/resgate' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.redemptions.shop',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/resgate/historico' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.redemptions.history',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/painel/minha-lista' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.wishlist.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/settings/payment' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'settings.payment',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'settings.payment.update',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/marketplace' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'marketplace.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/marketplace/vendas' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'marketplace.sales',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/stop-impersonating' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.impersonate.stop',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/settings/upload' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.upload',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.dashboard',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/balance' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.dashboard.balance',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/portal' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.portal.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/comunidade' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.social.feed.internal',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/courses/available' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.available',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/mentorships/available' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.mentorships.available',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/chat' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.chat.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/chat/list' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.chat.list',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/profile' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.profile.edit',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.profile.update',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/marketplace' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.marketplace.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/marketplace/pagamentos' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.marketplace.payments',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/marketplace/vendas' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.marketplace.sales',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/certificates' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.certificates.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/certificates/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.certificates.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/certificates/generate' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.certificates.generate',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/membro/perfil' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.membro.perfil',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/testimonials' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.testimonials.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/reviews' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.reviews.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/users/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/permissions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.permissions.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.permissions.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/permissions/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.permissions.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/plans' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.plans.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.plans.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/plans/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.plans.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/coupons' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.coupons.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.coupons.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/coupons/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.coupons.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/courses' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/courses/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/fonts' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fonts.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fonts.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/fonts/api/active' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fonts.api.active',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/faqs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.faqs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.faqs.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/faqs/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.faqs.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/activity-logs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.activity_logs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/activity-logs/clear' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.activity_logs.clear',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/events/feed' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.events.feed',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/events/calendar/settings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.events.calendar.settings',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/events' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.events.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.events.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/events/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.events.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/points-rules' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.points-rules.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.points-rules.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/points-rules/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.points-rules.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/mentorships' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.mentorships.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.mentorships.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/mentorships/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.mentorships.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/ranking' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.ranking',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/jobs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/jobs/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/redemptions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.redemptions.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.redemptions.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/redemptions/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.redemptions.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/upload/test' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.upload.test',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/mailtest' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.mailtest',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/upload/chunk' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.upload.chunk',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/upload/assemble' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.upload.assemble',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/mailtemplates' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.mailtemplates.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.mailtemplates.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/mailtemplates/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.mailtemplates.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/orders' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.orders.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/invoices' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.invoices.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.invoices.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/invoices/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.invoices.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/social' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.social.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
    ),
    2 => 
    array (
      0 => '{^(?|/a(?|pi/v1/(?|events/([^/]++)(*:36)|courses/([^/]++)(*:59)|mentorships/([^/]++)(*:86)|plans/([^/]++)(*:107))|dmin/(?|c(?|ron/([^/]++)(?|/(?|edit(*:151)|logs(*:163)|run(*:174))|(*:183))|hat/(?|start/([^/]++)(*:213)|([^/]++)(?|/message(?|s(*:244)|(*:252))|(*:261)))|ertificates/(?|send/([^/]++)(*:299)|([^/]++)/regenerate(*:326)|view/([^/]++)(*:347)|preview\\-html/([^/]++)(*:377)|([^/]++)(*:393))|ou(?|pons/([^/]++)(?|(*:423)|/edit(*:436)|(*:444))|rses/([^/]++)(?|(*:469)|/edit(*:482)|(*:490))))|testimonials/([^/]++)(?|/(?|edit(*:533)|approve(*:548)|reject(*:562))|(*:571))|re(?|views/([^/]++)(?|/(?|approve(*:613)|reject(*:627))|(*:636))|demptions/([^/]++)(?|/(?|approve(*:677)|cancel(*:691)|edit(*:703))|(*:712)))|users/([^/]++)(?|/(?|impersonate(*:754)|edit(*:766))|(*:775))|s(?|ettings(?|(?:/([^/]++))?(*:812)|(*:820)|/t(?|oggle(*:838)|est\\-(?|smtp(*:858)|gateway(*:873))))|ocial/([^/]++)(*:898))|p(?|ermissions/([^/]++)(?|(*:933)|/edit(*:946)|(*:954))|lans/([^/]++)(?|/(?|toggle\\-active(*:997)|edit(*:1009))|(*:1019))|oints\\-rules/([^/]++)(?|(*:1053)|/edit(*:1067)|(*:1076)))|f(?|onts/([^/]++)(*:1104)|aqs/([^/]++)(?|(*:1128)|/edit(*:1142)|(*:1151)))|events/([^/]++)(?|(*:1180)|/edit(*:1194)|(*:1203))|m(?|entorships/([^/]++)(?|(*:1239)|/edit(*:1253)|(*:1262))|ailtemplates/([^/]++)(?|/(?|edit(*:1304)|preview(*:1320)|send\\-preview(*:1342))|(*:1352)))|jobs/([^/]++)(?|(*:1379)|/edit(*:1393)|(*:1402))|orders/([^/]++)(?|/(?|refund(*:1440)|approve(*:1456)|cancel(*:1471)|invoice(*:1487))|(*:1497))|invoices/([^/]++)(?|/(?|send(*:1535)|pdf(*:1547)|edit(*:1560))|(*:1570)))|ssina(?|r/([^/]++)(?|(*:1602))|tura/sucesso/([^/]++)(*:1633))|uth/(?|redirect/([^/]++)(*:1667)|callback/([^/]++)(*:1693)))|/vagas\\-abertas/([^/]++)(*:1728)|/eventos/(?|([^/]++)(*:1757)|create(*:1772)|([^/]++)(?|/(?|edit(*:1800)|checkout(*:1817)|reservar(*:1834))|(*:1844))|pagamento/(?|sucesso/([^/]++)(*:1883)|pendente/([^/]++)(*:1909)|falha/([^/]++)(*:1932)))|/p(?|ost/([^/]++)(?|(*:1963)|/(?|re(?|act(*:1984)|port(*:1997))|comment(*:2014)|hide(*:2027)|share(?|(*:2044)|\\-to\\-user(*:2063))|unpublish(*:2082))|(*:2092))|a(?|ssword/reset/([^/]++)(*:2127)|inel/(?|admin/(?|settings(?|(?:/([^/]++))?(*:2178)|(*:2187)|/test\\-(?|smtp(*:2210)|gateway(*:2226)))|m(?|ailtemplates/([^/]++)(?|/(?|edit(*:2273)|preview(*:2289)|send\\-preview(*:2311))|(*:2321))|entorships/([^/]++)(?|(*:2353)|/edit(*:2367)|(*:2376)))|users/([^/]++)(?|(*:2404)|/edit(*:2418)|(*:2427))|p(?|lans/([^/]++)(?|/(?|toggle\\-active(*:2475)|edit(*:2488))|(*:2498))|oints\\-rules/([^/]++)(?|(*:2532)|/edit(*:2546)|(*:2555)))|orders/([^/]++)(?|/(?|refund(*:2594)|invoice(*:2610))|(*:2620))|invoices/([^/]++)(?|/(?|pdf(*:2657)|send(*:2670)|edit(*:2683))|(*:2693))|c(?|ou(?|pons/([^/]++)(?|(*:2728)|/edit(*:2742)|(*:2751))|rses/([^/]++)(?|(*:2777)|/(?|edit(*:2794)|lessons(?|/(?|reorder(*:2824)|([^/]++)(?|(*:2844)|/(?|details(*:2864)|attachments(?|(*:2887)|/([^/]++)(?|(*:2908))))))|(*:2922))|certificate/preview(*:2951))|(*:2961)))|ertificates/([^/]++)(?|(*:2995)|/edit(*:3009)|(*:3018))|ms/([^/]++)(*:3039))|events/([^/]++)(?|(*:3067)|/edit(*:3081)|(*:3090))|jobs/([^/]++)(?|(*:3116)|/edit(*:3130)|(*:3139))|redemptions/([^/]++)(?|/(?|approve(*:3183)|cancel(*:3198)|edit(*:3211))|(*:3221))|faqs/([^/]++)(?|(*:3247)|/edit(*:3261)|(*:3270))|testimonials/([^/]++)(?|(*:3304)|/(?|edit(*:3321)|approve(*:3337)|reject(*:3352))|(*:3362)))|certificados/([^/]++)(?|(*:3397)|/download(*:3415))|vagas/([^/]++)(?|(*:3442)|/candidatar(*:3462))|m(?|y\\-jobs/([^/]++)(?|(*:3495)|/edit(*:3509)|(*:3518))|inha\\-lista/toggle/([^/]++)(*:3555))|resgate/([^/]++)(*:3581)))|rofile/([^/]++)(*:3607)|/([^/]++)(*:3625))|/c(?|o(?|urses/(?|([^/]++)(*:3661)|create(*:3676)|([^/]++)(?|/(?|edit(*:3704)|complete(*:3721)|reviews(*:3737)|lessons(?|(*:3756)|/([^/]++)(?|(*:3777)|/(?|attachments(?|(*:3804)|/([^/]++)(?|/download(*:3834)|(*:3843)))|details(*:3861)|progress(*:3878)|bookmarks(?|(*:3899)|/([^/]++)(*:3917))))))|(*:3931)))|nnect(?|/([^/]++)(*:3959)|ion/(?|accept/([^/]++)(*:3990)|remove/([^/]++)(*:4014)|block/([^/]++)(*:4037)))|mment/([^/]++)(*:4062))|h(?|at/(?|start/([^/]++)(*:4096)|([^/]++)(?|/message(?|s(*:4128)|(*:4137))|(*:4147))|with/([^/]++)(?|(*:4173)|/message(*:4190)))|eckout/(?|([^/]++)(?|(*:4222))|success/([^/]++)(*:4248)|failure/([^/]++)(*:4273)|p(?|ending/([^/]++)(*:4301)|rocess\\-payment(*:4325))|mentorships/([^/]++)(?|(*:4358)))))|/mentorships/(?|([^/]++)(*:4395)|create(*:4410)|([^/]++)(?|/(?|edit(*:4438)|reviews(*:4454))|(*:4464)))|/notificacoes/(?|read(?:/([^/]++))?(*:4510)|([^/]++)(*:4527))|/webhook/mercadopago/([^/]++)(*:4566))/?$}sDu',
    ),
    3 => 
    array (
      36 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::sDNrz7nBQH11DJTx',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      59 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::O4WtzngDlwiCZrMo',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      86 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::S9lQWhN8kMg06XTJ',
          ),
          1 => 
          array (
            0 => 'mentorship',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      107 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::xLPftHWvnpqNdOGD',
          ),
          1 => 
          array (
            0 => 'plan',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      151 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.cron.edit',
          ),
          1 => 
          array (
            0 => 'task',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      163 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.cron.logs',
          ),
          1 => 
          array (
            0 => 'task',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      174 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.cron.run',
          ),
          1 => 
          array (
            0 => 'task',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      183 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.cron.update',
          ),
          1 => 
          array (
            0 => 'task',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.cron.destroy',
          ),
          1 => 
          array (
            0 => 'task',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      213 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.chat.start',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      244 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.chat.messages',
          ),
          1 => 
          array (
            0 => 'conversation',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      252 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.chat.message.store',
          ),
          1 => 
          array (
            0 => 'conversation',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      261 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.chat.show',
          ),
          1 => 
          array (
            0 => 'conversation',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      299 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.certificates.send',
          ),
          1 => 
          array (
            0 => 'certificate',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      326 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.certificates.regenerate',
          ),
          1 => 
          array (
            0 => 'certificate',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      347 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.certificates.view',
          ),
          1 => 
          array (
            0 => 'hash',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      377 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.certificates.preview-html',
          ),
          1 => 
          array (
            0 => 'hash',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      393 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.certificates.destroy',
          ),
          1 => 
          array (
            0 => 'certificate',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      423 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.coupons.show',
          ),
          1 => 
          array (
            0 => 'coupon',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      436 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.coupons.edit',
          ),
          1 => 
          array (
            0 => 'coupon',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      444 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.coupons.update',
          ),
          1 => 
          array (
            0 => 'coupon',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.coupons.destroy',
          ),
          1 => 
          array (
            0 => 'coupon',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      469 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.show',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      482 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.edit',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      490 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.update',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.destroy',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      533 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.testimonials.edit',
          ),
          1 => 
          array (
            0 => 'testimonial',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      548 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.testimonials.approve',
          ),
          1 => 
          array (
            0 => 'testimonial',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      562 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.testimonials.reject',
          ),
          1 => 
          array (
            0 => 'testimonial',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      571 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.testimonials.update',
          ),
          1 => 
          array (
            0 => 'testimonial',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.testimonials.destroy',
          ),
          1 => 
          array (
            0 => 'testimonial',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      613 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.reviews.approve',
          ),
          1 => 
          array (
            0 => 'review',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      627 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.reviews.reject',
          ),
          1 => 
          array (
            0 => 'review',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      636 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.reviews.destroy',
          ),
          1 => 
          array (
            0 => 'review',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      677 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.redemptions.approve',
          ),
          1 => 
          array (
            0 => 'redemption',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      691 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.redemptions.cancel',
          ),
          1 => 
          array (
            0 => 'redemption',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      703 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.redemptions.edit',
          ),
          1 => 
          array (
            0 => 'redemption',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      712 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.redemptions.show',
          ),
          1 => 
          array (
            0 => 'redemption',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.redemptions.update',
          ),
          1 => 
          array (
            0 => 'redemption',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'admin.redemptions.destroy',
          ),
          1 => 
          array (
            0 => 'redemption',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      754 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.impersonate',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      766 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.edit',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      775 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.show',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.update',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.destroy',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      812 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings',
            'group' => NULL,
          ),
          1 => 
          array (
            0 => 'group',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      820 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.update',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      838 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.toggle',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      858 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.test-smtp',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      873 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.test_gateway',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      898 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.social.destroy',
          ),
          1 => 
          array (
            0 => 'post',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      933 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.permissions.show',
          ),
          1 => 
          array (
            0 => 'permission',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      946 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.permissions.edit',
          ),
          1 => 
          array (
            0 => 'permission',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      954 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.permissions.update',
          ),
          1 => 
          array (
            0 => 'permission',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.permissions.destroy',
          ),
          1 => 
          array (
            0 => 'permission',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      997 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.plans.toggle-active',
          ),
          1 => 
          array (
            0 => 'plan',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1009 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.plans.edit',
          ),
          1 => 
          array (
            0 => 'plan',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1019 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.plans.show',
          ),
          1 => 
          array (
            0 => 'plan',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.plans.update',
          ),
          1 => 
          array (
            0 => 'plan',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'admin.plans.destroy',
          ),
          1 => 
          array (
            0 => 'plan',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1053 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.points-rules.show',
          ),
          1 => 
          array (
            0 => 'points_rule',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1067 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.points-rules.edit',
          ),
          1 => 
          array (
            0 => 'points_rule',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1076 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.points-rules.update',
          ),
          1 => 
          array (
            0 => 'points_rule',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.points-rules.destroy',
          ),
          1 => 
          array (
            0 => 'points_rule',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1104 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fonts.destroy',
          ),
          1 => 
          array (
            0 => 'font',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1128 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.faqs.show',
          ),
          1 => 
          array (
            0 => 'faq',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1142 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.faqs.edit',
          ),
          1 => 
          array (
            0 => 'faq',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1151 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.faqs.update',
          ),
          1 => 
          array (
            0 => 'faq',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.faqs.destroy',
          ),
          1 => 
          array (
            0 => 'faq',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1180 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.events.show',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1194 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.events.edit',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1203 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.events.update',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.events.destroy',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1239 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.mentorships.show',
          ),
          1 => 
          array (
            0 => 'mentorship',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1253 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.mentorships.edit',
          ),
          1 => 
          array (
            0 => 'mentorship',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1262 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.mentorships.update',
          ),
          1 => 
          array (
            0 => 'mentorship',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.mentorships.destroy',
          ),
          1 => 
          array (
            0 => 'mentorship',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1304 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.mailtemplates.edit',
          ),
          1 => 
          array (
            0 => 'mailtemplate',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1320 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.mailtemplates.preview',
          ),
          1 => 
          array (
            0 => 'mailtemplate',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1342 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.mailtemplates.sendpreview',
          ),
          1 => 
          array (
            0 => 'mailtemplate',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1352 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.mailtemplates.update',
          ),
          1 => 
          array (
            0 => 'mailtemplate',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.mailtemplates.destroy',
          ),
          1 => 
          array (
            0 => 'mailtemplate',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1379 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.show',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1393 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.edit',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1402 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.update',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.destroy',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1440 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.orders.refund',
          ),
          1 => 
          array (
            0 => 'order',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1456 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.orders.approve',
          ),
          1 => 
          array (
            0 => 'order',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1471 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.orders.cancel',
          ),
          1 => 
          array (
            0 => 'order',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1487 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.orders.invoice',
          ),
          1 => 
          array (
            0 => 'order',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1497 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.orders.show',
          ),
          1 => 
          array (
            0 => 'order',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1535 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.invoices.send',
          ),
          1 => 
          array (
            0 => 'invoice',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1547 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.invoices.pdf',
          ),
          1 => 
          array (
            0 => 'invoice',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1560 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.invoices.edit',
          ),
          1 => 
          array (
            0 => 'invoice',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1570 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.invoices.show',
          ),
          1 => 
          array (
            0 => 'invoice',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.invoices.update',
          ),
          1 => 
          array (
            0 => 'invoice',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'admin.invoices.destroy',
          ),
          1 => 
          array (
            0 => 'invoice',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1602 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'subscription.checkout',
          ),
          1 => 
          array (
            0 => 'plan',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'subscription.process',
          ),
          1 => 
          array (
            0 => 'plan',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1633 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'subscription.success',
          ),
          1 => 
          array (
            0 => 'order',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1667 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'social.redirect',
          ),
          1 => 
          array (
            0 => 'provider',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1693 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'social.callback',
          ),
          1 => 
          array (
            0 => 'provider',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1728 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'jobs.public.show',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1757 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'events.show',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1772 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'events.create',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1800 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'events.edit',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1817 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'events.checkout',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1834 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'events.reserve',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1844 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'events.update',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'events.destroy',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1883 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'events.payment.success',
          ),
          1 => 
          array (
            0 => 'order',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1909 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'events.payment.pending',
          ),
          1 => 
          array (
            0 => 'order',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1932 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'events.payment.failure',
          ),
          1 => 
          array (
            0 => 'order',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1963 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'social.post.public',
          ),
          1 => 
          array (
            0 => 'post',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1984 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'social.post.react',
          ),
          1 => 
          array (
            0 => 'post',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1997 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'social.post.report',
          ),
          1 => 
          array (
            0 => 'post',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2014 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'social.post.comment',
          ),
          1 => 
          array (
            0 => 'post',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2027 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'social.post.hide',
          ),
          1 => 
          array (
            0 => 'post',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2044 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'social.post.share',
          ),
          1 => 
          array (
            0 => 'post',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2063 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'social.post.share.user',
          ),
          1 => 
          array (
            0 => 'post',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2082 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'social.post.unpublish',
          ),
          1 => 
          array (
            0 => 'post',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2092 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'social.post.destroy',
          ),
          1 => 
          array (
            0 => 'post',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2127 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.reset',
          ),
          1 => 
          array (
            0 => 'token',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2178 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.settings',
            'group' => NULL,
          ),
          1 => 
          array (
            0 => 'group',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2187 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.settings.update',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2210 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.settings.test-smtp',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2226 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.settings.test-gateway',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2273 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.mailtemplates.edit',
          ),
          1 => 
          array (
            0 => 'mailtemplate',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2289 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.mailtemplates.preview',
          ),
          1 => 
          array (
            0 => 'mailtemplate',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2311 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.mailtemplates.sendpreview',
          ),
          1 => 
          array (
            0 => 'mailtemplate',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2321 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.mailtemplates.update',
          ),
          1 => 
          array (
            0 => 'mailtemplate',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.mailtemplates.destroy',
          ),
          1 => 
          array (
            0 => 'mailtemplate',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2353 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.mentorships.show',
          ),
          1 => 
          array (
            0 => 'mentorship',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2367 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.mentorships.edit',
          ),
          1 => 
          array (
            0 => 'mentorship',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2376 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.mentorships.update',
          ),
          1 => 
          array (
            0 => 'mentorship',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.mentorships.destroy',
          ),
          1 => 
          array (
            0 => 'mentorship',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2404 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.users.show',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2418 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.users.edit',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2427 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.users.update',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.users.destroy',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2475 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.plans.toggle-active',
          ),
          1 => 
          array (
            0 => 'plan',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2488 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.plans.edit',
          ),
          1 => 
          array (
            0 => 'plan',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2498 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.plans.show',
          ),
          1 => 
          array (
            0 => 'plan',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.plans.update',
          ),
          1 => 
          array (
            0 => 'plan',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.plans.destroy',
          ),
          1 => 
          array (
            0 => 'plan',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2532 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.points-rules.show',
          ),
          1 => 
          array (
            0 => 'points_rule',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2546 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.points-rules.edit',
          ),
          1 => 
          array (
            0 => 'points_rule',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2555 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.points-rules.update',
          ),
          1 => 
          array (
            0 => 'points_rule',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.points-rules.destroy',
          ),
          1 => 
          array (
            0 => 'points_rule',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2594 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.orders.refund',
          ),
          1 => 
          array (
            0 => 'order',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2610 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.orders.invoice',
          ),
          1 => 
          array (
            0 => 'order',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2620 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.orders.show',
          ),
          1 => 
          array (
            0 => 'order',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2657 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.invoices.pdf',
          ),
          1 => 
          array (
            0 => 'invoice',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2670 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.invoices.send',
          ),
          1 => 
          array (
            0 => 'invoice',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2683 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.invoices.edit',
          ),
          1 => 
          array (
            0 => 'invoice',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2693 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.invoices.show',
          ),
          1 => 
          array (
            0 => 'invoice',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.invoices.update',
          ),
          1 => 
          array (
            0 => 'invoice',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.invoices.destroy',
          ),
          1 => 
          array (
            0 => 'invoice',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2728 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.coupons.show',
          ),
          1 => 
          array (
            0 => 'coupon',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2742 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.coupons.edit',
          ),
          1 => 
          array (
            0 => 'coupon',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2751 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.coupons.update',
          ),
          1 => 
          array (
            0 => 'coupon',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.coupons.destroy',
          ),
          1 => 
          array (
            0 => 'coupon',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2777 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.courses.show',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2794 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.courses.edit',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2824 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.courses.lessons.reorder',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2844 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.courses.lessons.update',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'lesson',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.courses.lessons.destroy',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'lesson',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2864 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.courses.lessons.details',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'lesson',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2887 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.courses.lessons.attachments.store',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'lesson',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2908 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.courses.lessons.attachments.rename',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'lesson',
            2 => 'attachment',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.courses.lessons.attachments.destroy',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'lesson',
            2 => 'attachment',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2922 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.courses.lessons.store',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2951 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.courses.certificate.preview',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2961 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.courses.update',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.courses.destroy',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2995 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.certificates.show',
          ),
          1 => 
          array (
            0 => 'certificate',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3009 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.certificates.edit',
          ),
          1 => 
          array (
            0 => 'certificate',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3018 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.certificates.update',
          ),
          1 => 
          array (
            0 => 'certificate',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.certificates.destroy',
          ),
          1 => 
          array (
            0 => 'certificate',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3039 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.cms.update',
          ),
          1 => 
          array (
            0 => 'slug',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3067 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.events.show',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3081 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.events.edit',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3090 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.events.update',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.events.destroy',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3116 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.jobs.show',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3130 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.jobs.edit',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3139 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.jobs.update',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.jobs.destroy',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3183 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.redemptions.approve',
          ),
          1 => 
          array (
            0 => 'redemption',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3198 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.redemptions.cancel',
          ),
          1 => 
          array (
            0 => 'redemption',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3211 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.redemptions.edit',
          ),
          1 => 
          array (
            0 => 'redemption',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3221 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.redemptions.show',
          ),
          1 => 
          array (
            0 => 'redemption',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.redemptions.update',
          ),
          1 => 
          array (
            0 => 'redemption',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.redemptions.destroy',
          ),
          1 => 
          array (
            0 => 'redemption',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3247 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.faqs.show',
          ),
          1 => 
          array (
            0 => 'faq',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3261 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.faqs.edit',
          ),
          1 => 
          array (
            0 => 'faq',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3270 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.faqs.update',
          ),
          1 => 
          array (
            0 => 'faq',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.faqs.destroy',
          ),
          1 => 
          array (
            0 => 'faq',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3304 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.testimonials.show',
          ),
          1 => 
          array (
            0 => 'testimonial',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3321 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.testimonials.edit',
          ),
          1 => 
          array (
            0 => 'testimonial',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3337 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.testimonials.approve',
          ),
          1 => 
          array (
            0 => 'testimonial',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3352 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.testimonials.reject',
          ),
          1 => 
          array (
            0 => 'testimonial',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3362 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.testimonials.update',
          ),
          1 => 
          array (
            0 => 'testimonial',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.admin.testimonials.destroy',
          ),
          1 => 
          array (
            0 => 'testimonial',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3397 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.certificates.show',
          ),
          1 => 
          array (
            0 => 'certificate',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3415 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.certificates.download',
          ),
          1 => 
          array (
            0 => 'certificate',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3442 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.jobs.show',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3462 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.jobs.apply',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3495 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.my-jobs.show',
          ),
          1 => 
          array (
            0 => 'my_job',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3509 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.my-jobs.edit',
          ),
          1 => 
          array (
            0 => 'my_job',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3518 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.my-jobs.update',
          ),
          1 => 
          array (
            0 => 'my_job',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'panel.my-jobs.destroy',
          ),
          1 => 
          array (
            0 => 'my_job',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3555 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.wishlist.toggle',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3581 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'panel.redemptions.redeem',
          ),
          1 => 
          array (
            0 => 'item',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3607 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'social.profile',
          ),
          1 => 
          array (
            0 => 'username',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3625 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'share.product',
          ),
          1 => 
          array (
            0 => 'code',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3661 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.show',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3676 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.create',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3704 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.edit',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3721 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.complete',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3737 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.reviews.store',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3756 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.lessons.store',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3777 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.lessons.update',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'lesson',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'courses.lessons.destroy',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'lesson',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'courses.lessons.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'lesson',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3804 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.lessons.attachments.upload',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'lesson',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3834 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.lessons.attachments.download',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'lesson',
            2 => 'attachment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3843 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.lessons.attachments.destroy',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'lesson',
            2 => 'attachment',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'courses.lessons.attachments.rename',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'lesson',
            2 => 'attachment',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3861 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.lessons.details',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'lesson',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3878 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.lessons.progress.update',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'lesson',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3899 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.lessons.bookmarks.store',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'lesson',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3917 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.lessons.bookmarks.destroy',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'lesson',
            2 => 'bookmark',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3931 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.update',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'courses.destroy',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3959 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'connection.connect',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3990 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'connection.accept',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4014 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'connection.remove',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4037 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'connection.block',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4062 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'social.comment.destroy',
          ),
          1 => 
          array (
            0 => 'comment',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4096 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'chat.start',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4128 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'chat.messages',
          ),
          1 => 
          array (
            0 => 'conversation',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4137 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'chat.message.store',
          ),
          1 => 
          array (
            0 => 'conversation',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4147 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'chat.show',
          ),
          1 => 
          array (
            0 => 'conversation',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4173 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'chat.with.user',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4190 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'chat.with.user.message',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4222 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'checkout.show',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'checkout.process',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4248 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'checkout.success',
          ),
          1 => 
          array (
            0 => 'order',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4273 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'checkout.failure',
          ),
          1 => 
          array (
            0 => 'order',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4301 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'checkout.pending',
          ),
          1 => 
          array (
            0 => 'order',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4325 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'checkout.process_payment',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4358 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mentorships.checkout.show',
          ),
          1 => 
          array (
            0 => 'mentorship',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'mentorships.checkout.process',
          ),
          1 => 
          array (
            0 => 'mentorship',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4395 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mentorships.show',
          ),
          1 => 
          array (
            0 => 'mentorship',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4410 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mentorships.create',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4438 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mentorships.edit',
          ),
          1 => 
          array (
            0 => 'mentorship',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4454 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mentorships.reviews.store',
          ),
          1 => 
          array (
            0 => 'mentorship',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4464 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mentorships.update',
          ),
          1 => 
          array (
            0 => 'mentorship',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'mentorships.destroy',
          ),
          1 => 
          array (
            0 => 'mentorship',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4510 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'notifications.markRead',
            'id' => NULL,
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4527 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'notifications.destroy',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4566 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'webhook.mercadopago',
          ),
          1 => 
          array (
            0 => 'seller_id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => NULL,
          1 => NULL,
          2 => NULL,
          3 => NULL,
          4 => false,
          5 => false,
          6 => 0,
        ),
      ),
    ),
    4 => NULL,
  ),
  'attributes' => 
  array (
    'sanctum.csrf-cookie' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sanctum/csrf-cookie',
      'action' => 
      array (
        'uses' => 'Laravel\\Sanctum\\Http\\Controllers\\CsrfCookieController@show',
        'controller' => 'Laravel\\Sanctum\\Http\\Controllers\\CsrfCookieController@show',
        'namespace' => NULL,
        'prefix' => 'sanctum',
        'where' => 
        array (
        ),
        'middleware' => 
        array (
          0 => 'web',
        ),
        'as' => 'sanctum.csrf-cookie',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::uZ4h0jBV8Z5Ewff2' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/health',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\HealthController@show',
        'controller' => 'App\\Http\\Controllers\\Api\\HealthController@show',
        'namespace' => NULL,
        'prefix' => 'api/v1',
        'where' => 
        array (
        ),
        'as' => 'generated::uZ4h0jBV8Z5Ewff2',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::d9kkKZ8PBO8QADed' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/auth/register',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AuthController@register',
        'controller' => 'App\\Http\\Controllers\\Api\\AuthController@register',
        'namespace' => NULL,
        'prefix' => 'api/v1',
        'where' => 
        array (
        ),
        'as' => 'generated::d9kkKZ8PBO8QADed',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::DuFDBQJVwqbK9xZQ' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/auth/login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AuthController@login',
        'controller' => 'App\\Http\\Controllers\\Api\\AuthController@login',
        'namespace' => NULL,
        'prefix' => 'api/v1',
        'where' => 
        array (
        ),
        'as' => 'generated::DuFDBQJVwqbK9xZQ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::H0kaNXJEFQ8ignHE' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/events',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\EventApiController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\EventApiController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1',
        'where' => 
        array (
        ),
        'as' => 'generated::H0kaNXJEFQ8ignHE',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::sDNrz7nBQH11DJTx' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/events/{event}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\EventApiController@show',
        'controller' => 'App\\Http\\Controllers\\Api\\EventApiController@show',
        'namespace' => NULL,
        'prefix' => 'api/v1',
        'where' => 
        array (
        ),
        'as' => 'generated::sDNrz7nBQH11DJTx',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::V87ikNzycejHuLYq' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/courses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\CourseApiController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\CourseApiController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1',
        'where' => 
        array (
        ),
        'as' => 'generated::V87ikNzycejHuLYq',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::O4WtzngDlwiCZrMo' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/courses/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\CourseApiController@show',
        'controller' => 'App\\Http\\Controllers\\Api\\CourseApiController@show',
        'namespace' => NULL,
        'prefix' => 'api/v1',
        'where' => 
        array (
        ),
        'as' => 'generated::O4WtzngDlwiCZrMo',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::kTK1DiZSHiOOIu5F' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/mentorships',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\MentorshipApiController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\MentorshipApiController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1',
        'where' => 
        array (
        ),
        'as' => 'generated::kTK1DiZSHiOOIu5F',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::S9lQWhN8kMg06XTJ' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/mentorships/{mentorship}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\MentorshipApiController@show',
        'controller' => 'App\\Http\\Controllers\\Api\\MentorshipApiController@show',
        'namespace' => NULL,
        'prefix' => 'api/v1',
        'where' => 
        array (
        ),
        'as' => 'generated::S9lQWhN8kMg06XTJ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::WOCIUDbCozvkQpou' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/plans',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PlanApiController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\PlanApiController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1',
        'where' => 
        array (
        ),
        'as' => 'generated::WOCIUDbCozvkQpou',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::xLPftHWvnpqNdOGD' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/plans/{plan}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PlanApiController@show',
        'controller' => 'App\\Http\\Controllers\\Api\\PlanApiController@show',
        'namespace' => NULL,
        'prefix' => 'api/v1',
        'where' => 
        array (
        ),
        'as' => 'generated::xLPftHWvnpqNdOGD',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::b2sFoBJ0g4DjJgdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/testimonials',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\TestimonialApiController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\TestimonialApiController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1',
        'where' => 
        array (
        ),
        'as' => 'generated::b2sFoBJ0g4DjJgdf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::mXpYhg94o2v9mv49' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/auth/logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AuthController@logout',
        'controller' => 'App\\Http\\Controllers\\Api\\AuthController@logout',
        'namespace' => NULL,
        'prefix' => 'api/v1',
        'where' => 
        array (
        ),
        'as' => 'generated::mXpYhg94o2v9mv49',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::c2mhfrc0Amp4GvSg' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/me',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AuthController@me',
        'controller' => 'App\\Http\\Controllers\\Api\\AuthController@me',
        'namespace' => NULL,
        'prefix' => 'api/v1',
        'where' => 
        array (
        ),
        'as' => 'generated::c2mhfrc0Amp4GvSg',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.webhooks.mercadopago' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'api/v1/webhooks/mercadopago',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'excluded_middleware' => 
        array (
          0 => 'Illuminate\\Routing\\Middleware\\ThrottleRequests:api',
        ),
        'uses' => 'App\\Http\\Controllers\\PaymentWebhookController@mercadopago',
        'controller' => 'App\\Http\\Controllers\\PaymentWebhookController@mercadopago',
        'namespace' => NULL,
        'prefix' => 'api/v1/webhooks',
        'where' => 
        array (
        ),
        'as' => 'api.webhooks.mercadopago',
      ),
      'fallback' => false,
      'defaults' => 
      array (
        'seller_id' => 'platform',
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.webhooks.pagseguro' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'api/v1/webhooks/pagseguro',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'excluded_middleware' => 
        array (
          0 => 'Illuminate\\Routing\\Middleware\\ThrottleRequests:api',
        ),
        'uses' => 'App\\Http\\Controllers\\PaymentWebhookController@pagSeguro',
        'controller' => 'App\\Http\\Controllers\\PaymentWebhookController@pagSeguro',
        'namespace' => NULL,
        'prefix' => 'api/v1/webhooks',
        'where' => 
        array (
        ),
        'as' => 'api.webhooks.pagseguro',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.cron.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/cron',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CronController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\CronController@index',
        'as' => 'admin.cron.index',
        'namespace' => NULL,
        'prefix' => '/admin/cron',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.cron.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/cron/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CronController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\CronController@create',
        'as' => 'admin.cron.create',
        'namespace' => NULL,
        'prefix' => '/admin/cron',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.cron.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/cron',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CronController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\CronController@store',
        'as' => 'admin.cron.store',
        'namespace' => NULL,
        'prefix' => '/admin/cron',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.cron.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/cron/{task}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CronController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\CronController@edit',
        'as' => 'admin.cron.edit',
        'namespace' => NULL,
        'prefix' => '/admin/cron',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.cron.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/cron/{task}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CronController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\CronController@update',
        'as' => 'admin.cron.update',
        'namespace' => NULL,
        'prefix' => '/admin/cron',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.cron.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/cron/{task}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CronController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\CronController@destroy',
        'as' => 'admin.cron.destroy',
        'namespace' => NULL,
        'prefix' => '/admin/cron',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.cron.logs' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/cron/{task}/logs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CronController@logs',
        'controller' => 'App\\Http\\Controllers\\Admin\\CronController@logs',
        'as' => 'admin.cron.logs',
        'namespace' => NULL,
        'prefix' => '/admin/cron',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.cron.run' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/cron/{task}/run',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CronController@run',
        'controller' => 'App\\Http\\Controllers\\Admin\\CronController@run',
        'as' => 'admin.cron.run',
        'namespace' => NULL,
        'prefix' => '/admin/cron',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'subscription.checkout' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'assinar/{plan}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\SubscriptionController@checkout',
        'controller' => 'App\\Http\\Controllers\\SubscriptionController@checkout',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'subscription.checkout',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'subscription.process' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'assinar/{plan}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\SubscriptionController@process',
        'controller' => 'App\\Http\\Controllers\\SubscriptionController@process',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'subscription.process',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'subscription.success' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'assinatura/sucesso/{order}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\SubscriptionController@success',
        'controller' => 'App\\Http\\Controllers\\SubscriptionController@success',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'subscription.success',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'home' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '/',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\HomeController@index',
        'controller' => 'App\\Http\\Controllers\\HomeController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'home',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'portal' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'portal',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\HomeController@portal',
        'controller' => 'App\\Http\\Controllers\\HomeController@portal',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'portal',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'premium' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'premium',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\HomeController@premium',
        'controller' => 'App\\Http\\Controllers\\HomeController@premium',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'premium',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'testimonials.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'depoimentos',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TestimonialController@store',
        'controller' => 'App\\Http\\Controllers\\TestimonialController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'testimonials.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Y940t9NL8hdAfvcS' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'debug-test',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:97:"function () {
        return "<h1>Laravel is Running!</h1> PHP Version: " . phpversion();
    }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000007ca0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::Y940t9NL8hdAfvcS',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::fpeuJY6iQScC2pVn' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'limpar-cache',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:1040:"function () {
        $log = [];

        // 1. View Cache
        $viewPath = storage_path(\'framework/views\');
        $files = glob("$viewPath/*.php");
        foreach ($files as $file) {
            @unlink($file);
            $log[] = "View deletada: " . basename($file);
        }

        // 2. Route/Config Cache
        $bootstrapCache = base_path(\'bootstrap/cache\');
        $caches = [\'routes-v7.php\', \'routes.php\', \'config.php\', \'data.php\'];
        foreach ($caches as $c) {
            $f = "$bootstrapCache/$c";
            if (file_exists($f)) {
                @unlink($f);
                $log[] = "Cache deletado: $c";
            }
        }

        // 3. Artisan commands (fallback)
        try {
            \\Artisan::call(\'view:clear\');
            \\Artisan::call(\'route:clear\');
            \\Artisan::call(\'cache:clear\');
            $log[] = "Artisan commands executed.";
        } catch (\\Exception $e) {
            $log[] = "Artisan error: " . $e->getMessage();
        }


    }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000007cc0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::fpeuJY6iQScC2pVn',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sobre' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sobre',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:40:"fn() => view(\'site.institucional.sobre\')";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000007c80000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'sobre',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'manifesto' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'manifesto',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:44:"fn() => view(\'site.institucional.manifesto\')";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000007ce0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'manifesto',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'quem-somos' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'quem-somos',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:45:"fn() => view(\'site.institucional.quem-somos\')";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000007d00000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'quem-somos',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'como-funciona' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'como-funciona',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:48:"fn() => view(\'site.institucional.como-funciona\')";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000007d20000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'como-funciona',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'valores' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'valores',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:42:"fn() => view(\'site.institucional.valores\')";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000007d40000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'valores',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'contato' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'contato',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:42:"fn() => view(\'site.institucional.contato\')";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000007d60000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'contato',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'contato.send' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'contato',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'throttle:5,1',
        ),
        'uses' => 'App\\Http\\Controllers\\ContactController@send',
        'controller' => 'App\\Http\\Controllers\\ContactController@send',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'contato.send',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'membros' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'membros',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\MemberController@index',
        'controller' => 'App\\Http\\Controllers\\MemberController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'membros',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'jobs.public.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'vagas-abertas',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\OportunidadesTesteController@index',
        'controller' => 'App\\Http\\Controllers\\OportunidadesTesteController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'jobs.public.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'jobs.public.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'vagas-abertas/{job}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\JobPublicController@show',
        'controller' => 'App\\Http\\Controllers\\JobPublicController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'jobs.public.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'events.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'eventos',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\EventController@index',
        'controller' => 'App\\Http\\Controllers\\EventController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'events.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'events.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'eventos/{event}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\EventController@show',
        'controller' => 'App\\Http\\Controllers\\EventController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'events.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'events.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'eventos/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:events_create',
        ),
        'uses' => 'App\\Http\\Controllers\\EventController@create',
        'controller' => 'App\\Http\\Controllers\\EventController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'events.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'events.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'eventos',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:events_create',
        ),
        'uses' => 'App\\Http\\Controllers\\EventController@store',
        'controller' => 'App\\Http\\Controllers\\EventController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'events.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'events.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'eventos/{event}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:events_edit',
        ),
        'uses' => 'App\\Http\\Controllers\\EventController@edit',
        'controller' => 'App\\Http\\Controllers\\EventController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'events.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'events.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'eventos/{event}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:events_edit',
        ),
        'uses' => 'App\\Http\\Controllers\\EventController@update',
        'controller' => 'App\\Http\\Controllers\\EventController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'events.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'events.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'eventos/{event}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:events_delete',
        ),
        'uses' => 'App\\Http\\Controllers\\EventController@destroy',
        'controller' => 'App\\Http\\Controllers\\EventController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'events.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'events.checkout' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'eventos/{event}/checkout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\EventReservationController@checkout',
        'controller' => 'App\\Http\\Controllers\\EventReservationController@checkout',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'events.checkout',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'events.reserve' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'eventos/{event}/reservar',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\EventReservationController@reserve',
        'controller' => 'App\\Http\\Controllers\\EventReservationController@reserve',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'events.reserve',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'events.payment.success' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'eventos/pagamento/sucesso/{order}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\EventReservationController@paymentSuccess',
        'controller' => 'App\\Http\\Controllers\\EventReservationController@paymentSuccess',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'events.payment.success',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'events.payment.pending' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'eventos/pagamento/pendente/{order}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\EventReservationController@paymentPending',
        'controller' => 'App\\Http\\Controllers\\EventReservationController@paymentPending',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'events.payment.pending',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'events.payment.failure' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'eventos/pagamento/falha/{order}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\EventReservationController@paymentFailure',
        'controller' => 'App\\Http\\Controllers\\EventReservationController@paymentFailure',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'events.payment.failure',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::SglQ3oCdPfhjXBiK' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'service-worker.js',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:190:"function () {
    $path = public_path(\'service-worker.js\');
    abort_unless(file_exists($path), 404);
    return response()->file($path, [\'Content-Type\' => \'application/javascript\']);
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000007e80000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::SglQ3oCdPfhjXBiK',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::rMD05X7poA6x18Mc' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'favicon.ico',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:363:"function () {
    $custom = \\App\\Models\\Setting::get(\'favicon_image\');
    if ($custom && file_exists(public_path($custom))) {
        return response()->file(public_path($custom));
    }
    $default = public_path(\'img/logo.svg\');
    abort_unless(file_exists($default), 404);
    return response()->file($default, [\'Content-Type\' => \'image/svg+xml\']);
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000007ea0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::rMD05X7poA6x18Mc',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'social.post.public' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'post/{post}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\SocialController@publicPost',
        'controller' => 'App\\Http\\Controllers\\SocialController@publicPost',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'social.post.public',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'login' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:26:"fn() => view(\'auth.login\')";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000007ed0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'login',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::ZYKaKLJCdxH0JLqP' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\LoginController@authenticate',
        'controller' => 'App\\Http\\Controllers\\Auth\\LoginController@authenticate',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::ZYKaKLJCdxH0JLqP',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\LoginController@logout',
        'controller' => 'App\\Http\\Controllers\\Auth\\LoginController@logout',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'logout',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'register' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'register',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:29:"fn() => view(\'auth.register\')";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000007f10000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'register',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::HCVBQGM5bv9JFyV3' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'register',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\RegisterController@store',
        'controller' => 'App\\Http\\Controllers\\Auth\\RegisterController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::HCVBQGM5bv9JFyV3',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.request' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'password/forgot',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ForgotPasswordController@showLinkRequestForm',
        'controller' => 'App\\Http\\Controllers\\Auth\\ForgotPasswordController@showLinkRequestForm',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.request',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.email' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'password/email',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ForgotPasswordController@sendResetLinkEmail',
        'controller' => 'App\\Http\\Controllers\\Auth\\ForgotPasswordController@sendResetLinkEmail',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.email',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.reset' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'password/reset/{token}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ResetPasswordController@showResetForm',
        'controller' => 'App\\Http\\Controllers\\Auth\\ResetPasswordController@showResetForm',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.reset',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'password/reset',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ResetPasswordController@reset',
        'controller' => 'App\\Http\\Controllers\\Auth\\ResetPasswordController@reset',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'social.redirect' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'auth/redirect/{provider}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\SocialAuthController@redirect',
        'controller' => 'App\\Http\\Controllers\\Auth\\SocialAuthController@redirect',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'social.redirect',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'social.callback' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'auth/callback/{provider}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\SocialAuthController@callback',
        'controller' => 'App\\Http\\Controllers\\Auth\\SocialAuthController@callback',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'social.callback',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'upload.file' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'upload',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\UploadController@upload',
        'controller' => 'App\\Http\\Controllers\\UploadController@upload',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'upload.file',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::m1hyXhqJodt56aly' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'webhook/mercadopago',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\PaymentWebhookController@mercadopago',
        'controller' => 'App\\Http\\Controllers\\PaymentWebhookController@mercadopago',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::m1hyXhqJodt56aly',
      ),
      'fallback' => false,
      'defaults' => 
      array (
        'seller_id' => 'platform',
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::ui613NBrPxkPVaNk' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'webhook/pagseguro',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\PaymentWebhookController@pagSeguro',
        'controller' => 'App\\Http\\Controllers\\PaymentWebhookController@pagSeguro',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::ui613NBrPxkPVaNk',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'install.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'install',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\InstallController@index',
        'controller' => 'App\\Http\\Controllers\\InstallController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'install.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'install.run' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'install/run',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\InstallController@run',
        'controller' => 'App\\Http\\Controllers\\InstallController@run',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'install.run',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'install.test-connection' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'install/test-connection',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\InstallController@testConnection',
        'controller' => 'App\\Http\\Controllers\\InstallController@testConnection',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'install.test-connection',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::PPm5nZAtmDJonQVD' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'install/run',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:42:"fn() => redirect()->route(\'install.index\')";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000008000000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::PPm5nZAtmDJonQVD',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'install.index.legacy' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'backend/install',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\InstallController@index',
        'controller' => 'App\\Http\\Controllers\\InstallController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'install.index.legacy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'install.run.legacy' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'backend/install/run',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\InstallController@run',
        'controller' => 'App\\Http\\Controllers\\InstallController@run',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'install.run.legacy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'install.test-connection.legacy' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'backend/install/test-connection',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\InstallController@testConnection',
        'controller' => 'App\\Http\\Controllers\\InstallController@testConnection',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'install.test-connection.legacy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::4GqEnr2fh4oTPha2' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'backend/install/run',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:49:"fn() => redirect()->route(\'install.index.legacy\')";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000008050000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::4GqEnr2fh4oTPha2',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.interactions.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/interactions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\InteractionController@store',
        'controller' => 'App\\Http\\Controllers\\InteractionController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'api.interactions.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.satisfactions.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/satisfactions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\SatisfactionController@store',
        'controller' => 'App\\Http\\Controllers\\SatisfactionController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'api.satisfactions.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.ranking.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/ranking',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\RankingController@index',
        'controller' => 'App\\Http\\Controllers\\RankingController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'api.ranking.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'manifest' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'manifest.webmanifest',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\PwaController@manifest',
        'controller' => 'App\\Http\\Controllers\\PwaController@manifest',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'manifest',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'offline' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'offline',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:23:"fn() => view(\'offline\')";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"000000000000080b0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'offline',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sitemap' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sitemap.xml',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\SitemapController@index',
        'controller' => 'App\\Http\\Controllers\\SitemapController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'sitemap',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::i08xFzNSVgyDnWSg' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'robots.txt',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:136:"function () {
    return response("User-agent: *\\nAllow: /\\nSitemap: " . url(\'sitemap.xml\'), 200, [\'Content-Type\' => \'text/plain\']);
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"000000000000080e0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::i08xFzNSVgyDnWSg',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'courses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\CourseController@index',
        'controller' => 'App\\Http\\Controllers\\CourseController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'courses/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\CourseController@show',
        'controller' => 'App\\Http\\Controllers\\CourseController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'courses/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:courses_create',
        ),
        'uses' => 'App\\Http\\Controllers\\CourseController@create',
        'controller' => 'App\\Http\\Controllers\\CourseController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'courses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:courses_create',
        ),
        'uses' => 'App\\Http\\Controllers\\CourseController@store',
        'controller' => 'App\\Http\\Controllers\\CourseController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'courses/{course}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:courses_edit',
        ),
        'uses' => 'App\\Http\\Controllers\\CourseController@edit',
        'controller' => 'App\\Http\\Controllers\\CourseController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'courses/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:courses_edit',
        ),
        'uses' => 'App\\Http\\Controllers\\CourseController@update',
        'controller' => 'App\\Http\\Controllers\\CourseController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'courses/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:courses_delete',
        ),
        'uses' => 'App\\Http\\Controllers\\CourseController@destroy',
        'controller' => 'App\\Http\\Controllers\\CourseController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.complete' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'courses/{course}/complete',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:courses_delete',
        ),
        'uses' => 'App\\Http\\Controllers\\CourseController@complete',
        'controller' => 'App\\Http\\Controllers\\CourseController@complete',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.complete',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mentorships.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'mentorships',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\MentorshipController@index',
        'controller' => 'App\\Http\\Controllers\\MentorshipController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'mentorships.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mentorships.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'mentorships/{mentorship}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\MentorshipController@show',
        'controller' => 'App\\Http\\Controllers\\MentorshipController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'mentorships.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mentorships.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'mentorships/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:mentorships_create',
        ),
        'uses' => 'App\\Http\\Controllers\\MentorshipController@create',
        'controller' => 'App\\Http\\Controllers\\MentorshipController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'mentorships.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mentorships.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'mentorships',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:mentorships_create',
        ),
        'uses' => 'App\\Http\\Controllers\\MentorshipController@store',
        'controller' => 'App\\Http\\Controllers\\MentorshipController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'mentorships.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mentorships.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'mentorships/{mentorship}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:mentorships_edit',
        ),
        'uses' => 'App\\Http\\Controllers\\MentorshipController@edit',
        'controller' => 'App\\Http\\Controllers\\MentorshipController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'mentorships.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mentorships.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'mentorships/{mentorship}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:mentorships_edit',
        ),
        'uses' => 'App\\Http\\Controllers\\MentorshipController@update',
        'controller' => 'App\\Http\\Controllers\\MentorshipController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'mentorships.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mentorships.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'mentorships/{mentorship}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:mentorships_delete',
        ),
        'uses' => 'App\\Http\\Controllers\\MentorshipController@destroy',
        'controller' => 'App\\Http\\Controllers\\MentorshipController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'mentorships.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.reviews.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'courses/{course}/reviews',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:courses_review',
        ),
        'uses' => 'App\\Http\\Controllers\\ItemReviewController@storeCourse',
        'controller' => 'App\\Http\\Controllers\\ItemReviewController@storeCourse',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.reviews.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mentorships.reviews.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'mentorships/{mentorship}/reviews',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:mentorships_review',
        ),
        'uses' => 'App\\Http\\Controllers\\ItemReviewController@storeMentorship',
        'controller' => 'App\\Http\\Controllers\\ItemReviewController@storeMentorship',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'mentorships.reviews.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.lessons.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'courses/{course}/lessons',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:courses_lessons_create',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@store',
        'controller' => 'App\\Http\\Controllers\\LessonController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.lessons.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.lessons.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'courses/{course}/lessons/{lesson}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:courses_lessons_edit',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@update',
        'controller' => 'App\\Http\\Controllers\\LessonController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.lessons.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.lessons.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'courses/{course}/lessons/{lesson}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:courses_lessons_delete',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@destroy',
        'controller' => 'App\\Http\\Controllers\\LessonController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.lessons.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.lessons.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'courses/{course}/lessons/{lesson}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'check.feature:courses_lessons_access',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@show',
        'controller' => 'App\\Http\\Controllers\\LessonController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.lessons.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.lessons.attachments.upload' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'courses/{course}/lessons/{lesson}/attachments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:courses_lessons_attachments_upload',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@uploadAttachment',
        'controller' => 'App\\Http\\Controllers\\LessonController@uploadAttachment',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.lessons.attachments.upload',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.lessons.attachments.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'courses/{course}/lessons/{lesson}/attachments/{attachment}/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:courses_lessons_attachments_download',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@downloadAttachment',
        'controller' => 'App\\Http\\Controllers\\LessonController@downloadAttachment',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.lessons.attachments.download',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.lessons.attachments.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'courses/{course}/lessons/{lesson}/attachments/{attachment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:courses_lessons_attachments_delete',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@deleteAttachment',
        'controller' => 'App\\Http\\Controllers\\LessonController@deleteAttachment',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.lessons.attachments.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.lessons.attachments.rename' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'courses/{course}/lessons/{lesson}/attachments/{attachment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:courses_lessons_attachments_edit',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@renameAttachment',
        'controller' => 'App\\Http\\Controllers\\LessonController@renameAttachment',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.lessons.attachments.rename',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.lessons.details' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'courses/{course}/lessons/{lesson}/details',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:courses_lessons_access',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@getDetails',
        'controller' => 'App\\Http\\Controllers\\LessonController@getDetails',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.lessons.details',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.lessons.progress.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'courses/{course}/lessons/{lesson}/progress',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:courses_lessons_access',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@updatePlaybackProgress',
        'controller' => 'App\\Http\\Controllers\\LessonController@updatePlaybackProgress',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.lessons.progress.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.lessons.bookmarks.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'courses/{course}/lessons/{lesson}/bookmarks',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:courses_lessons_access',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@storeBookmark',
        'controller' => 'App\\Http\\Controllers\\LessonController@storeBookmark',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.lessons.bookmarks.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.lessons.bookmarks.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'courses/{course}/lessons/{lesson}/bookmarks/{bookmark}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.feature:courses_lessons_access',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@destroyBookmark',
        'controller' => 'App\\Http\\Controllers\\LessonController@destroyBookmark',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'courses.lessons.bookmarks.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'gateway.mercadopago.connect' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'gateway/mercadopago/connect',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\GatewayAccountController@connect',
        'controller' => 'App\\Http\\Controllers\\GatewayAccountController@connect',
        'as' => 'gateway.mercadopago.connect',
        'namespace' => NULL,
        'prefix' => '/gateway/mercadopago',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'gateway.mercadopago.callback' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'gateway/mercadopago/callback',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\GatewayAccountController@callback',
        'controller' => 'App\\Http\\Controllers\\GatewayAccountController@callback',
        'as' => 'gateway.mercadopago.callback',
        'namespace' => NULL,
        'prefix' => '/gateway/mercadopago',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'social.feed' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'feed',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:community',
        ),
        'uses' => 'App\\Http\\Controllers\\SocialController@feed',
        'controller' => 'App\\Http\\Controllers\\SocialController@feed',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'social.feed',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'social.profile' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'profile/{username}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:community',
        ),
        'uses' => 'App\\Http\\Controllers\\SocialController@profile',
        'controller' => 'App\\Http\\Controllers\\SocialController@profile',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'social.profile',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'connection.connect' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'connect/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:community',
        ),
        'uses' => 'App\\Http\\Controllers\\ConnectionController@connect',
        'controller' => 'App\\Http\\Controllers\\ConnectionController@connect',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'connection.connect',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'connection.accept' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'connection/accept/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:community',
        ),
        'uses' => 'App\\Http\\Controllers\\ConnectionController@accept',
        'controller' => 'App\\Http\\Controllers\\ConnectionController@accept',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'connection.accept',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'connection.remove' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'connection/remove/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:community',
        ),
        'uses' => 'App\\Http\\Controllers\\ConnectionController@remove',
        'controller' => 'App\\Http\\Controllers\\ConnectionController@remove',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'connection.remove',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'connection.block' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'connection/block/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:community',
        ),
        'uses' => 'App\\Http\\Controllers\\ConnectionController@block',
        'controller' => 'App\\Http\\Controllers\\ConnectionController@block',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'connection.block',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'connection.notifications' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'connection/notifications',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:community',
        ),
        'uses' => 'App\\Http\\Controllers\\ConnectionController@notifications',
        'controller' => 'App\\Http\\Controllers\\ConnectionController@notifications',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'connection.notifications',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'social.post.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'post',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:community',
        ),
        'uses' => 'App\\Http\\Controllers\\SocialController@storePost',
        'controller' => 'App\\Http\\Controllers\\SocialController@storePost',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'social.post.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'social.post.react' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'post/{post}/react',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:community',
        ),
        'uses' => 'App\\Http\\Controllers\\SocialController@toggleReaction',
        'controller' => 'App\\Http\\Controllers\\SocialController@toggleReaction',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'social.post.react',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'social.post.comment' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'post/{post}/comment',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:community',
        ),
        'uses' => 'App\\Http\\Controllers\\SocialController@storeComment',
        'controller' => 'App\\Http\\Controllers\\SocialController@storeComment',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'social.post.comment',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'social.comment.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'comment/{comment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:community',
        ),
        'uses' => 'App\\Http\\Controllers\\SocialController@destroyComment',
        'controller' => 'App\\Http\\Controllers\\SocialController@destroyComment',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'social.comment.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'social.post.hide' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'post/{post}/hide',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:community',
        ),
        'uses' => 'App\\Http\\Controllers\\SocialController@hidePost',
        'controller' => 'App\\Http\\Controllers\\SocialController@hidePost',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'social.post.hide',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'social.post.share' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'post/{post}/share',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:community',
        ),
        'uses' => 'App\\Http\\Controllers\\SocialController@sharePost',
        'controller' => 'App\\Http\\Controllers\\SocialController@sharePost',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'social.post.share',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'social.post.share.user' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'post/{post}/share-to-user',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:community',
        ),
        'uses' => 'App\\Http\\Controllers\\SocialController@sharePostToUser',
        'controller' => 'App\\Http\\Controllers\\SocialController@sharePostToUser',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'social.post.share.user',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'social.post.report' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'post/{post}/report',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:community',
        ),
        'uses' => 'App\\Http\\Controllers\\SocialController@reportPost',
        'controller' => 'App\\Http\\Controllers\\SocialController@reportPost',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'social.post.report',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'social.post.unpublish' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'post/{post}/unpublish',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:community',
        ),
        'uses' => 'App\\Http\\Controllers\\SocialController@unpublishPost',
        'controller' => 'App\\Http\\Controllers\\SocialController@unpublishPost',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'social.post.unpublish',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'social.post.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'post/{post}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:community',
        ),
        'uses' => 'App\\Http\\Controllers\\SocialController@destroyPost',
        'controller' => 'App\\Http\\Controllers\\SocialController@destroyPost',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'social.post.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'chat.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'chat',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:chat',
          4 => 'check.connection',
        ),
        'uses' => 'App\\Http\\Controllers\\ChatController@index',
        'controller' => 'App\\Http\\Controllers\\ChatController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'chat.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'chat.start' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'chat/start/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:chat',
          4 => 'check.connection',
        ),
        'uses' => 'App\\Http\\Controllers\\ChatController@start',
        'controller' => 'App\\Http\\Controllers\\ChatController@start',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'chat.start',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'chat.list' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'chat/list',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:chat',
          4 => 'check.connection',
        ),
        'uses' => 'App\\Http\\Controllers\\ChatController@list',
        'controller' => 'App\\Http\\Controllers\\ChatController@list',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'chat.list',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'chat.messages' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'chat/{conversation}/messages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:chat',
          4 => 'check.connection',
        ),
        'uses' => 'App\\Http\\Controllers\\ChatController@getMessages',
        'controller' => 'App\\Http\\Controllers\\ChatController@getMessages',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'chat.messages',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'chat.message.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'chat/{conversation}/message',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:chat',
          4 => 'check.connection',
        ),
        'uses' => 'App\\Http\\Controllers\\ChatController@storeMessage',
        'controller' => 'App\\Http\\Controllers\\ChatController@storeMessage',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'chat.message.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'chat.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'chat/{conversation}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:chat',
          4 => 'check.connection',
        ),
        'uses' => 'App\\Http\\Controllers\\ChatController@show',
        'controller' => 'App\\Http\\Controllers\\ChatController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'chat.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'chat.with.user' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'chat/with/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:chat',
          4 => 'check.connection',
        ),
        'uses' => 'App\\Http\\Controllers\\ChatController@withUser',
        'controller' => 'App\\Http\\Controllers\\ChatController@withUser',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'chat.with.user',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'chat.with.user.message' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'chat/with/{user}/message',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:chat',
          4 => 'check.connection',
        ),
        'uses' => 'App\\Http\\Controllers\\ChatController@storeMessageWithUser',
        'controller' => 'App\\Http\\Controllers\\ChatController@storeMessageWithUser',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'chat.with.user.message',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'notifications.hub' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/notifications/hub',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:chat',
          4 => 'check.connection',
        ),
        'uses' => 'App\\Http\\Controllers\\NotificationHubController@index',
        'controller' => 'App\\Http\\Controllers\\NotificationHubController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'notifications.hub',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'notifications.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'notificacoes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:chat',
          4 => 'check.connection',
        ),
        'uses' => 'App\\Http\\Controllers\\NotificationController@index',
        'controller' => 'App\\Http\\Controllers\\NotificationController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'notifications.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'notifications.markRead' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'notificacoes/read/{id?}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:chat',
          4 => 'check.connection',
        ),
        'uses' => 'App\\Http\\Controllers\\NotificationController@markAsRead',
        'controller' => 'App\\Http\\Controllers\\NotificationController@markAsRead',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'notifications.markRead',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'notifications.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'notificacoes/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.feature:chat',
          4 => 'check.connection',
        ),
        'uses' => 'App\\Http\\Controllers\\NotificationController@destroy',
        'controller' => 'App\\Http\\Controllers\\NotificationController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'notifications.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\DashboardController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\DashboardController@index',
        'as' => 'panel.dashboard',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.dashboard.stats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/dashboard/stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\DashboardController@stats',
        'controller' => 'App\\Http\\Controllers\\Panel\\DashboardController@stats',
        'as' => 'panel.dashboard.stats',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DashboardController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\DashboardController@index',
        'as' => 'panel.admin.dashboard',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.settings' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/settings/{group?}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SettingController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\SettingController@index',
        'as' => 'panel.admin.settings',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.settings.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/settings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SettingController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\SettingController@update',
        'as' => 'panel.admin.settings.update',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.settings.test-smtp' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/settings/test-smtp',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SettingController@testSmtp',
        'controller' => 'App\\Http\\Controllers\\Admin\\SettingController@testSmtp',
        'as' => 'panel.admin.settings.test-smtp',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.settings.test-gateway' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/settings/test-gateway',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SettingController@testGateway',
        'controller' => 'App\\Http\\Controllers\\Admin\\SettingController@testGateway',
        'as' => 'panel.admin.settings.test-gateway',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.mailtemplates.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/mailtemplates',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@index',
        'as' => 'panel.admin.mailtemplates.index',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.mailtemplates.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/mailtemplates/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@create',
        'as' => 'panel.admin.mailtemplates.create',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.mailtemplates.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/mailtemplates',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@store',
        'as' => 'panel.admin.mailtemplates.store',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.mailtemplates.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/mailtemplates/{mailtemplate}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@edit',
        'as' => 'panel.admin.mailtemplates.edit',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.mailtemplates.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'painel/admin/mailtemplates/{mailtemplate}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@update',
        'as' => 'panel.admin.mailtemplates.update',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.mailtemplates.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'painel/admin/mailtemplates/{mailtemplate}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@destroy',
        'as' => 'panel.admin.mailtemplates.destroy',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.mailtemplates.preview' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/mailtemplates/{mailtemplate}/preview',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@preview',
        'controller' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@preview',
        'as' => 'panel.admin.mailtemplates.preview',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.mailtemplates.sendpreview' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/mailtemplates/{mailtemplate}/send-preview',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@sendPreview',
        'controller' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@sendPreview',
        'as' => 'panel.admin.mailtemplates.sendpreview',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.users.index',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\UserController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\UserController@index',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.users.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/users/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.users.create',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\UserController@create',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\UserController@create',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.users.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.users.store',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\UserController@store',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\UserController@store',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.users.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.users.show',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\UserController@show',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\UserController@show',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.users.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/users/{user}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.users.edit',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\UserController@edit',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\UserController@edit',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.users.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'painel/admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.users.update',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\UserController@update',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\UserController@update',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.users.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'painel/admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.users.destroy',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\UserController@destroy',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\UserController@destroy',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.plans.toggle-active' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/plans/{plan}/toggle-active',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\PlanController@toggleActive',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\PlanController@toggleActive',
        'as' => 'panel.admin.plans.toggle-active',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.plans.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/plans',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.plans.index',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\PlanController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\PlanController@index',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.plans.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/plans/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.plans.create',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\PlanController@create',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\PlanController@create',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.plans.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/plans',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.plans.store',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\PlanController@store',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\PlanController@store',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.plans.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/plans/{plan}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.plans.show',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\PlanController@show',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\PlanController@show',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.plans.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/plans/{plan}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.plans.edit',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\PlanController@edit',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\PlanController@edit',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.plans.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'painel/admin/plans/{plan}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.plans.update',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\PlanController@update',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\PlanController@update',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.plans.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'painel/admin/plans/{plan}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.plans.destroy',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\PlanController@destroy',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\PlanController@destroy',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.orders.refund' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/orders/{order}/refund',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\OrderController@refund',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\OrderController@refund',
        'as' => 'panel.admin.orders.refund',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.orders.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/orders',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.orders.index',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\OrderController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\OrderController@index',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.orders.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/orders/{order}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.orders.show',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\OrderController@show',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\OrderController@show',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.orders.invoice' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/orders/{order}/invoice',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@issueForOrder',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@issueForOrder',
        'as' => 'panel.admin.orders.invoice',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.invoices.pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/invoices/{invoice}/pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@pdf',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@pdf',
        'as' => 'panel.admin.invoices.pdf',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.invoices.send' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/invoices/{invoice}/send',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@send',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@send',
        'as' => 'panel.admin.invoices.send',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.invoices.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/invoices',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.invoices.index',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@index',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.invoices.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/invoices/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.invoices.create',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@create',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@create',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.invoices.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/invoices',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.invoices.store',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@store',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@store',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.invoices.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/invoices/{invoice}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.invoices.show',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@show',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@show',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.invoices.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/invoices/{invoice}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.invoices.edit',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@edit',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@edit',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.invoices.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'painel/admin/invoices/{invoice}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.invoices.update',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@update',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@update',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.invoices.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'painel/admin/invoices/{invoice}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.invoices.destroy',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@destroy',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\InvoiceController@destroy',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.coupons.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/coupons',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.coupons.index',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CouponController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CouponController@index',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.coupons.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/coupons/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.coupons.create',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CouponController@create',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CouponController@create',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.coupons.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/coupons',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.coupons.store',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CouponController@store',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CouponController@store',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.coupons.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/coupons/{coupon}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.coupons.show',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CouponController@show',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CouponController@show',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.coupons.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/coupons/{coupon}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.coupons.edit',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CouponController@edit',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CouponController@edit',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.coupons.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'painel/admin/coupons/{coupon}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.coupons.update',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CouponController@update',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CouponController@update',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.coupons.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'painel/admin/coupons/{coupon}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.coupons.destroy',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CouponController@destroy',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CouponController@destroy',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.courses.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/courses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.courses.index',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CourseController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CourseController@index',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.courses.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/courses/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.courses.create',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CourseController@create',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CourseController@create',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.courses.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/courses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.courses.store',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CourseController@store',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CourseController@store',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.courses.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/courses/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.courses.show',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CourseController@show',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CourseController@show',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.courses.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/courses/{course}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.courses.edit',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CourseController@edit',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CourseController@edit',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.courses.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'painel/admin/courses/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.courses.update',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CourseController@update',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CourseController@update',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.courses.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'painel/admin/courses/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.courses.destroy',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CourseController@destroy',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CourseController@destroy',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.courses.lessons.reorder' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/courses/{course}/lessons/reorder',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CourseController@reorderLessons',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CourseController@reorderLessons',
        'as' => 'panel.admin.courses.lessons.reorder',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.mentorships.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/mentorships',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.mentorships.index',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\MentorshipController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\MentorshipController@index',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.mentorships.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/mentorships/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.mentorships.create',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\MentorshipController@create',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\MentorshipController@create',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.mentorships.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/mentorships',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.mentorships.store',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\MentorshipController@store',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\MentorshipController@store',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.mentorships.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/mentorships/{mentorship}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.mentorships.show',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\MentorshipController@show',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\MentorshipController@show',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.mentorships.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/mentorships/{mentorship}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.mentorships.edit',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\MentorshipController@edit',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\MentorshipController@edit',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.mentorships.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'painel/admin/mentorships/{mentorship}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.mentorships.update',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\MentorshipController@update',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\MentorshipController@update',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.mentorships.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'painel/admin/mentorships/{mentorship}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.mentorships.destroy',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\MentorshipController@destroy',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\MentorshipController@destroy',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.events.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/events',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.events.index',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\EventController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\EventController@index',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.events.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/events/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.events.create',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\EventController@create',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\EventController@create',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.events.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/events',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.events.store',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\EventController@store',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\EventController@store',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.events.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/events/{event}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.events.show',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\EventController@show',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\EventController@show',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.events.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/events/{event}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.events.edit',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\EventController@edit',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\EventController@edit',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.events.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'painel/admin/events/{event}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.events.update',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\EventController@update',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\EventController@update',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.events.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'painel/admin/events/{event}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.events.destroy',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\EventController@destroy',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\EventController@destroy',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.certificates.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/certificates',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.certificates.index',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CertificateController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CertificateController@index',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.certificates.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/certificates/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.certificates.create',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CertificateController@create',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CertificateController@create',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.certificates.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/certificates',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.certificates.store',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CertificateController@store',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CertificateController@store',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.certificates.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/certificates/{certificate}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.certificates.show',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CertificateController@show',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CertificateController@show',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.certificates.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/certificates/{certificate}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.certificates.edit',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CertificateController@edit',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CertificateController@edit',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.certificates.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'painel/admin/certificates/{certificate}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.certificates.update',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CertificateController@update',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CertificateController@update',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.certificates.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'painel/admin/certificates/{certificate}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.certificates.destroy',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CertificateController@destroy',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CertificateController@destroy',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.jobs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/jobs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.jobs.index',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\JobController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\JobController@index',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.jobs.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/jobs/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.jobs.create',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\JobController@create',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\JobController@create',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.jobs.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/jobs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.jobs.store',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\JobController@store',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\JobController@store',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.jobs.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/jobs/{job}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.jobs.show',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\JobController@show',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\JobController@show',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.jobs.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/jobs/{job}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.jobs.edit',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\JobController@edit',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\JobController@edit',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.jobs.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'painel/admin/jobs/{job}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.jobs.update',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\JobController@update',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\JobController@update',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.jobs.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'painel/admin/jobs/{job}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.jobs.destroy',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\JobController@destroy',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\JobController@destroy',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.redemptions.approve' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/redemptions/{redemption}/approve',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\RedemptionController@approve',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\RedemptionController@approve',
        'as' => 'panel.admin.redemptions.approve',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.redemptions.cancel' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/redemptions/{redemption}/cancel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\RedemptionController@cancel',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\RedemptionController@cancel',
        'as' => 'panel.admin.redemptions.cancel',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.redemptions.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/redemptions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.redemptions.index',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\RedemptionController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\RedemptionController@index',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.redemptions.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/redemptions/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.redemptions.create',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\RedemptionController@create',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\RedemptionController@create',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.redemptions.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/redemptions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.redemptions.store',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\RedemptionController@store',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\RedemptionController@store',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.redemptions.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/redemptions/{redemption}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.redemptions.show',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\RedemptionController@show',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\RedemptionController@show',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.redemptions.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/redemptions/{redemption}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.redemptions.edit',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\RedemptionController@edit',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\RedemptionController@edit',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.redemptions.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'painel/admin/redemptions/{redemption}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.redemptions.update',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\RedemptionController@update',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\RedemptionController@update',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.redemptions.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'painel/admin/redemptions/{redemption}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.redemptions.destroy',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\RedemptionController@destroy',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\RedemptionController@destroy',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.faqs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/faqs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.faqs.index',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\FaqController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\FaqController@index',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.faqs.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/faqs/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.faqs.create',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\FaqController@create',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\FaqController@create',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.faqs.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/faqs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.faqs.store',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\FaqController@store',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\FaqController@store',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.faqs.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/faqs/{faq}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.faqs.show',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\FaqController@show',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\FaqController@show',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.faqs.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/faqs/{faq}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.faqs.edit',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\FaqController@edit',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\FaqController@edit',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.faqs.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'painel/admin/faqs/{faq}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.faqs.update',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\FaqController@update',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\FaqController@update',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.faqs.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'painel/admin/faqs/{faq}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.faqs.destroy',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\FaqController@destroy',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\FaqController@destroy',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.testimonials.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/testimonials',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.testimonials.index',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\TestimonialController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\TestimonialController@index',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.testimonials.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/testimonials/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.testimonials.create',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\TestimonialController@create',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\TestimonialController@create',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.testimonials.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/testimonials',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.testimonials.store',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\TestimonialController@store',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\TestimonialController@store',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.testimonials.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/testimonials/{testimonial}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.testimonials.show',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\TestimonialController@show',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\TestimonialController@show',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.testimonials.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/testimonials/{testimonial}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.testimonials.edit',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\TestimonialController@edit',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\TestimonialController@edit',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.testimonials.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'painel/admin/testimonials/{testimonial}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.testimonials.update',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\TestimonialController@update',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\TestimonialController@update',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.testimonials.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'painel/admin/testimonials/{testimonial}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.testimonials.destroy',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\TestimonialController@destroy',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\TestimonialController@destroy',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.testimonials.approve' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/testimonials/{testimonial}/approve',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\TestimonialController@approve',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\TestimonialController@approve',
        'as' => 'panel.admin.testimonials.approve',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.testimonials.reject' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/testimonials/{testimonial}/reject',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\TestimonialController@reject',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\TestimonialController@reject',
        'as' => 'panel.admin.testimonials.reject',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.cms.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/cms',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CMSController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CMSController@index',
        'as' => 'panel.admin.cms.index',
        'namespace' => NULL,
        'prefix' => 'painel/admin/cms',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.cms.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/cms/{slug}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CMSController@update',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CMSController@update',
        'as' => 'panel.admin.cms.update',
        'namespace' => NULL,
        'prefix' => 'painel/admin/cms',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.logs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/logs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\ActivityLogController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\ActivityLogController@index',
        'as' => 'panel.admin.logs.index',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.logs.clear' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/logs/clear',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\ActivityLogController@clear',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\ActivityLogController@clear',
        'as' => 'panel.admin.logs.clear',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.points-rules.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/points-rules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.points-rules.index',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\PointsRuleController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\PointsRuleController@index',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.points-rules.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/points-rules/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.points-rules.create',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\PointsRuleController@create',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\PointsRuleController@create',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.points-rules.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/points-rules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.points-rules.store',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\PointsRuleController@store',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\PointsRuleController@store',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.points-rules.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/points-rules/{points_rule}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.points-rules.show',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\PointsRuleController@show',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\PointsRuleController@show',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.points-rules.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/points-rules/{points_rule}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.points-rules.edit',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\PointsRuleController@edit',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\PointsRuleController@edit',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.points-rules.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'painel/admin/points-rules/{points_rule}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.points-rules.update',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\PointsRuleController@update',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\PointsRuleController@update',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.points-rules.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'painel/admin/points-rules/{points_rule}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'panel.admin.points-rules.destroy',
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\PointsRuleController@destroy',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\PointsRuleController@destroy',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.ranking.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/ranking',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\RankingController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\RankingController@index',
        'as' => 'panel.admin.ranking.index',
        'namespace' => NULL,
        'prefix' => 'painel/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.courses.lessons.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/courses/{course}/lessons',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@store',
        'controller' => 'App\\Http\\Controllers\\LessonController@store',
        'as' => 'panel.admin.courses.lessons.store',
        'namespace' => NULL,
        'prefix' => 'painel/admin/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.courses.lessons.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'painel/admin/courses/{course}/lessons/{lesson}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@update',
        'controller' => 'App\\Http\\Controllers\\LessonController@update',
        'as' => 'panel.admin.courses.lessons.update',
        'namespace' => NULL,
        'prefix' => 'painel/admin/courses/{course}/lessons/{lesson}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.courses.lessons.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'painel/admin/courses/{course}/lessons/{lesson}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@destroy',
        'controller' => 'App\\Http\\Controllers\\LessonController@destroy',
        'as' => 'panel.admin.courses.lessons.destroy',
        'namespace' => NULL,
        'prefix' => 'painel/admin/courses/{course}/lessons/{lesson}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.courses.lessons.details' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/admin/courses/{course}/lessons/{lesson}/details',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@getDetails',
        'controller' => 'App\\Http\\Controllers\\LessonController@getDetails',
        'as' => 'panel.admin.courses.lessons.details',
        'namespace' => NULL,
        'prefix' => 'painel/admin/courses/{course}/lessons/{lesson}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.courses.lessons.attachments.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/courses/{course}/lessons/{lesson}/attachments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@uploadAttachment',
        'controller' => 'App\\Http\\Controllers\\LessonController@uploadAttachment',
        'as' => 'panel.admin.courses.lessons.attachments.store',
        'namespace' => NULL,
        'prefix' => 'painel/admin/courses/{course}/lessons/{lesson}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.courses.lessons.attachments.rename' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'painel/admin/courses/{course}/lessons/{lesson}/attachments/{attachment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@renameAttachment',
        'controller' => 'App\\Http\\Controllers\\LessonController@renameAttachment',
        'as' => 'panel.admin.courses.lessons.attachments.rename',
        'namespace' => NULL,
        'prefix' => 'painel/admin/courses/{course}/lessons/{lesson}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.courses.lessons.attachments.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'painel/admin/courses/{course}/lessons/{lesson}/attachments/{attachment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\LessonController@deleteAttachment',
        'controller' => 'App\\Http\\Controllers\\LessonController@deleteAttachment',
        'as' => 'panel.admin.courses.lessons.attachments.destroy',
        'namespace' => NULL,
        'prefix' => 'painel/admin/courses/{course}/lessons/{lesson}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.admin.courses.certificate.preview' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/admin/courses/{course}/certificate/preview',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\Admin\\CourseController@certificatePreview',
        'controller' => 'App\\Http\\Controllers\\Panel\\Admin\\CourseController@certificatePreview',
        'as' => 'panel.admin.courses.certificate.preview',
        'namespace' => NULL,
        'prefix' => 'painel/admin/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.theme.toggle' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/theme/toggle',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\ThemeController@update',
        'controller' => 'App\\Http\\Controllers\\Panel\\ThemeController@update',
        'as' => 'panel.theme.toggle',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.profile.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/perfil',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\ProfileController@edit',
        'controller' => 'App\\Http\\Controllers\\Panel\\ProfileController@edit',
        'as' => 'panel.profile.edit',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.profile.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/perfil',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\ProfileController@update',
        'controller' => 'App\\Http\\Controllers\\Panel\\ProfileController@update',
        'as' => 'panel.profile.update',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.certificates.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/certificados',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\CertificateController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\CertificateController@index',
        'as' => 'panel.certificates.index',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.certificates.generate' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/certificados/gerar',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\CertificateController@generate',
        'controller' => 'App\\Http\\Controllers\\Panel\\CertificateController@generate',
        'as' => 'panel.certificates.generate',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.certificates.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/certificados/{certificate}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\CertificateController@show',
        'controller' => 'App\\Http\\Controllers\\Panel\\CertificateController@show',
        'as' => 'panel.certificates.show',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.certificates.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/certificados/{certificate}/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\CertificateController@download',
        'controller' => 'App\\Http\\Controllers\\Panel\\CertificateController@download',
        'as' => 'panel.certificates.download',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.marketplace.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/marketplace',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.marketplace.seller',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\MarketplaceController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\MarketplaceController@index',
        'as' => 'panel.marketplace.index',
        'namespace' => NULL,
        'prefix' => 'painel/marketplace',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.marketplace.payments' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/marketplace/pagamentos',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.marketplace.seller',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\MarketplaceController@payments',
        'controller' => 'App\\Http\\Controllers\\Panel\\MarketplaceController@payments',
        'as' => 'panel.marketplace.payments',
        'namespace' => NULL,
        'prefix' => 'painel/marketplace',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.marketplace.payments.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/marketplace/pagamentos/configurar',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.marketplace.seller',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\MarketplaceController@editPayment',
        'controller' => 'App\\Http\\Controllers\\Panel\\MarketplaceController@editPayment',
        'as' => 'panel.marketplace.payments.edit',
        'namespace' => NULL,
        'prefix' => 'painel/marketplace',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.marketplace.payments.test' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/marketplace/pagamentos/testar',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.marketplace.seller',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\MarketplaceController@testCredentials',
        'controller' => 'App\\Http\\Controllers\\Panel\\MarketplaceController@testCredentials',
        'as' => 'panel.marketplace.payments.test',
        'namespace' => NULL,
        'prefix' => 'painel/marketplace',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.marketplace.sales' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/marketplace/vendas',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
          3 => 'check.marketplace.seller',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\MarketplaceController@sales',
        'controller' => 'App\\Http\\Controllers\\Panel\\MarketplaceController@sales',
        'as' => 'panel.marketplace.sales',
        'namespace' => NULL,
        'prefix' => 'painel/marketplace',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.jobs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/vagas',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\JobController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\JobController@index',
        'as' => 'panel.jobs.index',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.jobs.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/vagas/{job}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\JobController@show',
        'controller' => 'App\\Http\\Controllers\\Panel\\JobController@show',
        'as' => 'panel.jobs.show',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.jobs.apply' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/vagas/{job}/candidatar',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\JobController@apply',
        'controller' => 'App\\Http\\Controllers\\Panel\\JobController@apply',
        'as' => 'panel.jobs.apply',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.my-jobs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/my-jobs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'as' => 'panel.my-jobs.index',
        'uses' => 'App\\Http\\Controllers\\Panel\\MyJobController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\MyJobController@index',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.my-jobs.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/my-jobs/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'as' => 'panel.my-jobs.create',
        'uses' => 'App\\Http\\Controllers\\Panel\\MyJobController@create',
        'controller' => 'App\\Http\\Controllers\\Panel\\MyJobController@create',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.my-jobs.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/my-jobs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'as' => 'panel.my-jobs.store',
        'uses' => 'App\\Http\\Controllers\\Panel\\MyJobController@store',
        'controller' => 'App\\Http\\Controllers\\Panel\\MyJobController@store',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.my-jobs.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/my-jobs/{my_job}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'as' => 'panel.my-jobs.show',
        'uses' => 'App\\Http\\Controllers\\Panel\\MyJobController@show',
        'controller' => 'App\\Http\\Controllers\\Panel\\MyJobController@show',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.my-jobs.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/my-jobs/{my_job}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'as' => 'panel.my-jobs.edit',
        'uses' => 'App\\Http\\Controllers\\Panel\\MyJobController@edit',
        'controller' => 'App\\Http\\Controllers\\Panel\\MyJobController@edit',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.my-jobs.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'painel/my-jobs/{my_job}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'as' => 'panel.my-jobs.update',
        'uses' => 'App\\Http\\Controllers\\Panel\\MyJobController@update',
        'controller' => 'App\\Http\\Controllers\\Panel\\MyJobController@update',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.my-jobs.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'painel/my-jobs/{my_job}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'as' => 'panel.my-jobs.destroy',
        'uses' => 'App\\Http\\Controllers\\Panel\\MyJobController@destroy',
        'controller' => 'App\\Http\\Controllers\\Panel\\MyJobController@destroy',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.redemptions.shop' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/resgate',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\RedemptionItemController@index',
        'controller' => 'App\\Http\\Controllers\\RedemptionItemController@index',
        'as' => 'panel.redemptions.shop',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.redemptions.history' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/resgate/historico',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\RedemptionItemController@history',
        'controller' => 'App\\Http\\Controllers\\RedemptionItemController@history',
        'as' => 'panel.redemptions.history',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.redemptions.redeem' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/resgate/{item}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\RedemptionItemController@redeem',
        'controller' => 'App\\Http\\Controllers\\RedemptionItemController@redeem',
        'as' => 'panel.redemptions.redeem',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.wishlist.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'painel/minha-lista',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\WishlistController@index',
        'controller' => 'App\\Http\\Controllers\\Panel\\WishlistController@index',
        'as' => 'panel.wishlist.index',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'panel.wishlist.toggle' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'painel/minha-lista/toggle/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Panel\\WishlistController@toggle',
        'controller' => 'App\\Http\\Controllers\\Panel\\WishlistController@toggle',
        'as' => 'panel.wishlist.toggle',
        'namespace' => NULL,
        'prefix' => '/painel',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'settings.payment' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'settings/payment',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:85:"function () {
        return redirect()->route(\'panel.marketplace.payments\');
    }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000008ec0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'settings.payment',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'settings.payment.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'settings/payment',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\GatewayAccountController@update',
        'controller' => 'App\\Http\\Controllers\\GatewayAccountController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'settings.payment.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'checkout.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'checkout/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\CheckoutController@show',
        'controller' => 'App\\Http\\Controllers\\CheckoutController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'checkout.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'checkout.process' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'checkout/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\CheckoutController@process',
        'controller' => 'App\\Http\\Controllers\\CheckoutController@process',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'checkout.process',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'checkout.success' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'checkout/success/{order}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:32:"fn() => view(\'checkout.success\')";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000008ef0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'checkout.success',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'checkout.failure' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'checkout/failure/{order}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:32:"fn() => view(\'checkout.failure\')";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000008f10000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'checkout.failure',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'checkout.pending' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'checkout/pending/{order}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:32:"fn() => view(\'checkout.pending\')";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000008f30000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'checkout.pending',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'checkout.process_payment' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'checkout/process-payment',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\CheckoutController@processPayment',
        'controller' => 'App\\Http\\Controllers\\CheckoutController@processPayment',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'checkout.process_payment',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mentorships.checkout.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'checkout/mentorships/{mentorship}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\MentorshipCheckoutController@show',
        'controller' => 'App\\Http\\Controllers\\MentorshipCheckoutController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'mentorships.checkout.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mentorships.checkout.process' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'checkout/mentorships/{mentorship}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\MentorshipCheckoutController@process',
        'controller' => 'App\\Http\\Controllers\\MentorshipCheckoutController@process',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'mentorships.checkout.process',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'webhook.mercadopago' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'webhook/mercadopago/{seller_id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\PaymentWebhookController@mercadopago',
        'controller' => 'App\\Http\\Controllers\\PaymentWebhookController@mercadopago',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'webhook.mercadopago',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'share.product' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'p/{code}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\ShareController@product',
        'controller' => 'App\\Http\\Controllers\\ShareController@product',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'share.product',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'marketplace.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'marketplace',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\MarketplaceController@index',
        'controller' => 'App\\Http\\Controllers\\MarketplaceController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'marketplace.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'marketplace.sales' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'marketplace/vendas',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:82:"function () {
        return redirect()->route(\'panel.marketplace.sales\');
    }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000008fd0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'marketplace.sales',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.impersonate.stop' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/stop-impersonating',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ImpersonateController@stop',
        'controller' => 'App\\Http\\Controllers\\Admin\\ImpersonateController@stop',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.impersonate.stop',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.upload' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/settings/upload',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SettingController@uploadFile',
        'controller' => 'App\\Http\\Controllers\\Admin\\SettingController@uploadFile',
        'as' => 'admin.settings.upload',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DashboardController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\DashboardController@index',
        'as' => 'admin.dashboard',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.dashboard.balance' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/balance',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DashboardController@getMpBalance',
        'controller' => 'App\\Http\\Controllers\\Admin\\DashboardController@getMpBalance',
        'as' => 'admin.dashboard.balance',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.portal.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/portal',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MemberController@portal',
        'controller' => 'App\\Http\\Controllers\\Admin\\MemberController@portal',
        'as' => 'admin.portal.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.social.feed.internal' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/comunidade',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:community_access',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MemberController@socialFeed',
        'controller' => 'App\\Http\\Controllers\\Admin\\MemberController@socialFeed',
        'as' => 'admin.social.feed.internal',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.available' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/available',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CourseController@available',
        'controller' => 'App\\Http\\Controllers\\Admin\\CourseController@available',
        'as' => 'admin.courses.available',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.mentorships.available' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/mentorships/available',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:mentorships_access',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MentorshipController@available',
        'controller' => 'App\\Http\\Controllers\\Admin\\MentorshipController@available',
        'as' => 'admin.mentorships.available',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.chat.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/chat',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:chat_access',
          6 => 'check.connection',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ChatController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\ChatController@index',
        'as' => 'admin.chat.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.chat.start' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/chat/start/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:chat_access',
          6 => 'check.connection',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ChatController@start',
        'controller' => 'App\\Http\\Controllers\\Admin\\ChatController@start',
        'as' => 'admin.chat.start',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.chat.list' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/chat/list',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:chat_access',
          6 => 'check.connection',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ChatController@list',
        'controller' => 'App\\Http\\Controllers\\Admin\\ChatController@list',
        'as' => 'admin.chat.list',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.chat.messages' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/chat/{conversation}/messages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:chat_access',
          6 => 'check.connection',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ChatController@getMessages',
        'controller' => 'App\\Http\\Controllers\\Admin\\ChatController@getMessages',
        'as' => 'admin.chat.messages',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.chat.message.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/chat/{conversation}/message',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:chat_access',
          6 => 'check.connection',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ChatController@storeMessage',
        'controller' => 'App\\Http\\Controllers\\Admin\\ChatController@storeMessage',
        'as' => 'admin.chat.message.store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.chat.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/chat/{conversation}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:chat_access',
          6 => 'check.connection',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ChatController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\ChatController@show',
        'as' => 'admin.chat.show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.profile.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ProfileController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\ProfileController@edit',
        'as' => 'admin.profile.edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.profile.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ProfileController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\ProfileController@update',
        'as' => 'admin.profile.update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.marketplace.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/marketplace',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MarketplaceController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\MarketplaceController@index',
        'as' => 'admin.marketplace.index',
        'namespace' => NULL,
        'prefix' => 'admin/marketplace',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.marketplace.payments' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/marketplace/pagamentos',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MarketplaceController@payments',
        'controller' => 'App\\Http\\Controllers\\Admin\\MarketplaceController@payments',
        'as' => 'admin.marketplace.payments',
        'namespace' => NULL,
        'prefix' => 'admin/marketplace',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.marketplace.sales' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/marketplace/vendas',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MarketplaceController@sales',
        'controller' => 'App\\Http\\Controllers\\Admin\\MarketplaceController@sales',
        'as' => 'admin.marketplace.sales',
        'namespace' => NULL,
        'prefix' => 'admin/marketplace',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.certificates.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/certificates',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:certificates_access',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CertificateController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\CertificateController@index',
        'as' => 'admin.certificates.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.certificates.send' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/certificates/send/{certificate}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:certificates_create',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CertificateController@sendEmail',
        'controller' => 'App\\Http\\Controllers\\Admin\\CertificateController@sendEmail',
        'as' => 'admin.certificates.send',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.certificates.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/certificates/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:certificates_create',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CertificateController@createForm',
        'controller' => 'App\\Http\\Controllers\\Admin\\CertificateController@createForm',
        'as' => 'admin.certificates.create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.certificates.generate' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/certificates/generate',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:certificates_generate',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CertificateController@generate',
        'controller' => 'App\\Http\\Controllers\\Admin\\CertificateController@generate',
        'as' => 'admin.certificates.generate',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.certificates.regenerate' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/certificates/{certificate}/regenerate',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:certificates_generate',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CertificateController@regenerate',
        'controller' => 'App\\Http\\Controllers\\Admin\\CertificateController@regenerate',
        'as' => 'admin.certificates.regenerate',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.certificates.view' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/certificates/view/{hash}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:certificates_access',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CertificateController@view',
        'controller' => 'App\\Http\\Controllers\\Admin\\CertificateController@view',
        'as' => 'admin.certificates.view',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.certificates.preview-html' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/certificates/preview-html/{hash}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:certificates_access',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CertificateController@previewHtml',
        'controller' => 'App\\Http\\Controllers\\Admin\\CertificateController@previewHtml',
        'as' => 'admin.certificates.preview-html',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.certificates.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/certificates/{certificate}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:certificates_delete',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CertificateController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\CertificateController@destroy',
        'as' => 'admin.certificates.destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.membro.perfil' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/membro/perfil',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ProfileController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\ProfileController@edit',
        'as' => 'admin.membro.perfil',
        'namespace' => NULL,
        'prefix' => 'admin/membro',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.testimonials.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/testimonials',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:testimonials_access',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\TestimonialController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\TestimonialController@index',
        'as' => 'admin.testimonials.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.testimonials.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/testimonials/{testimonial}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:testimonials_edit',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\TestimonialController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\TestimonialController@edit',
        'as' => 'admin.testimonials.edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.testimonials.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/testimonials/{testimonial}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:testimonials_edit',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\TestimonialController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\TestimonialController@update',
        'as' => 'admin.testimonials.update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.testimonials.approve' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/testimonials/{testimonial}/approve',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:testimonials_approve',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\TestimonialController@approve',
        'controller' => 'App\\Http\\Controllers\\Admin\\TestimonialController@approve',
        'as' => 'admin.testimonials.approve',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.testimonials.reject' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/testimonials/{testimonial}/reject',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:testimonials_reject',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\TestimonialController@reject',
        'controller' => 'App\\Http\\Controllers\\Admin\\TestimonialController@reject',
        'as' => 'admin.testimonials.reject',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.testimonials.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/testimonials/{testimonial}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:testimonials_delete',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\TestimonialController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\TestimonialController@destroy',
        'as' => 'admin.testimonials.destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.reviews.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/reviews',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:reviews_access',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ItemReviewController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\ItemReviewController@index',
        'as' => 'admin.reviews.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.reviews.approve' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/reviews/{review}/approve',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:reviews_approve',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ItemReviewController@approve',
        'controller' => 'App\\Http\\Controllers\\Admin\\ItemReviewController@approve',
        'as' => 'admin.reviews.approve',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.reviews.reject' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/reviews/{review}/reject',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:reviews_reject',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ItemReviewController@reject',
        'controller' => 'App\\Http\\Controllers\\Admin\\ItemReviewController@reject',
        'as' => 'admin.reviews.reject',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.reviews.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/reviews/{review}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'check.feature:reviews_delete',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ItemReviewController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\ItemReviewController@destroy',
        'as' => 'admin.reviews.destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.impersonate' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/{user}/impersonate',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ImpersonateController@impersonate',
        'controller' => 'App\\Http\\Controllers\\Admin\\ImpersonateController@impersonate',
        'as' => 'admin.users.impersonate',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/settings/{group?}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SettingController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\SettingController@index',
        'as' => 'admin.settings',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/settings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SettingController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\SettingController@update',
        'as' => 'admin.settings.update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.toggle' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/settings/toggle',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SettingController@toggle',
        'controller' => 'App\\Http\\Controllers\\Admin\\SettingController@toggle',
        'as' => 'admin.settings.toggle',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.test-smtp' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/settings/test-smtp',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SettingController@testSmtp',
        'controller' => 'App\\Http\\Controllers\\Admin\\SettingController@testSmtp',
        'as' => 'admin.settings.test-smtp',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.test_gateway' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/settings/test-gateway',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SettingController@testGateway',
        'controller' => 'App\\Http\\Controllers\\Admin\\SettingController@testGateway',
        'as' => 'admin.settings.test_gateway',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.users.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.users.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.users.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.users.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/{user}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.users.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.users.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.users.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.permissions.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/permissions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:permissions_access',
        ),
        'as' => 'admin.permissions.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\PermissionController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\PermissionController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.permissions.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/permissions/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:permissions_access',
        ),
        'as' => 'admin.permissions.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\PermissionController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\PermissionController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.permissions.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/permissions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:permissions_access',
        ),
        'as' => 'admin.permissions.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\PermissionController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\PermissionController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.permissions.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/permissions/{permission}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:permissions_access',
        ),
        'as' => 'admin.permissions.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\PermissionController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\PermissionController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.permissions.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/permissions/{permission}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:permissions_access',
        ),
        'as' => 'admin.permissions.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\PermissionController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\PermissionController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.permissions.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/permissions/{permission}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:permissions_access',
        ),
        'as' => 'admin.permissions.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\PermissionController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\PermissionController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.permissions.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/permissions/{permission}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:permissions_access',
        ),
        'as' => 'admin.permissions.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\PermissionController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\PermissionController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.plans.toggle-active' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/plans/{plan}/toggle-active',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:plans_access',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\PlanController@toggleActive',
        'controller' => 'App\\Http\\Controllers\\Admin\\PlanController@toggleActive',
        'as' => 'admin.plans.toggle-active',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.plans.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/plans',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:plans_access',
        ),
        'as' => 'admin.plans.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\PlanController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\PlanController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.plans.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/plans/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:plans_access',
        ),
        'as' => 'admin.plans.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\PlanController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\PlanController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.plans.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/plans',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:plans_access',
        ),
        'as' => 'admin.plans.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\PlanController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\PlanController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.plans.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/plans/{plan}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:plans_access',
        ),
        'as' => 'admin.plans.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\PlanController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\PlanController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.plans.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/plans/{plan}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:plans_access',
        ),
        'as' => 'admin.plans.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\PlanController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\PlanController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.plans.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/plans/{plan}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:plans_access',
        ),
        'as' => 'admin.plans.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\PlanController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\PlanController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.plans.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/plans/{plan}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:plans_access',
        ),
        'as' => 'admin.plans.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\PlanController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\PlanController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.coupons.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/coupons',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:coupons_access',
        ),
        'as' => 'admin.coupons.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\CouponController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\CouponController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.coupons.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/coupons/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:coupons_access',
        ),
        'as' => 'admin.coupons.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\CouponController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\CouponController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.coupons.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/coupons',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:coupons_access',
        ),
        'as' => 'admin.coupons.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\CouponController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\CouponController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.coupons.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/coupons/{coupon}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:coupons_access',
        ),
        'as' => 'admin.coupons.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\CouponController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\CouponController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.coupons.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/coupons/{coupon}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:coupons_access',
        ),
        'as' => 'admin.coupons.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\CouponController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\CouponController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.coupons.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/coupons/{coupon}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:coupons_access',
        ),
        'as' => 'admin.coupons.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\CouponController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\CouponController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.coupons.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/coupons/{coupon}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:coupons_access',
        ),
        'as' => 'admin.coupons.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\CouponController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\CouponController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:courses_access',
        ),
        'as' => 'admin.courses.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\CourseController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\CourseController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:courses_access',
        ),
        'as' => 'admin.courses.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\CourseController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\CourseController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:courses_access',
        ),
        'as' => 'admin.courses.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\CourseController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\CourseController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:courses_access',
        ),
        'as' => 'admin.courses.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\CourseController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\CourseController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:courses_access',
        ),
        'as' => 'admin.courses.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\CourseController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\CourseController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/courses/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:courses_access',
        ),
        'as' => 'admin.courses.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\CourseController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\CourseController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/courses/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:courses_access',
        ),
        'as' => 'admin.courses.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\CourseController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\CourseController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fonts.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/fonts',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:fonts_access',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CustomFontController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\CustomFontController@index',
        'as' => 'admin.fonts.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fonts.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/fonts',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:fonts_create',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CustomFontController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\CustomFontController@store',
        'as' => 'admin.fonts.store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fonts.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/fonts/{font}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:fonts_delete',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CustomFontController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\CustomFontController@destroy',
        'as' => 'admin.fonts.destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fonts.api.active' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/fonts/api/active',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:fonts_access',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CustomFontController@getActiveFonts',
        'controller' => 'App\\Http\\Controllers\\Admin\\CustomFontController@getActiveFonts',
        'as' => 'admin.fonts.api.active',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.faqs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/faqs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:faqs_access',
        ),
        'as' => 'admin.faqs.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\FaqController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\FaqController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.faqs.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/faqs/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:faqs_access',
        ),
        'as' => 'admin.faqs.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\FaqController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\FaqController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.faqs.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/faqs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:faqs_access',
        ),
        'as' => 'admin.faqs.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\FaqController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\FaqController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.faqs.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/faqs/{faq}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:faqs_access',
        ),
        'as' => 'admin.faqs.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\FaqController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\FaqController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.faqs.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/faqs/{faq}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:faqs_access',
        ),
        'as' => 'admin.faqs.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\FaqController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\FaqController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.faqs.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/faqs/{faq}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:faqs_access',
        ),
        'as' => 'admin.faqs.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\FaqController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\FaqController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.faqs.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/faqs/{faq}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:faqs_access',
        ),
        'as' => 'admin.faqs.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\FaqController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\FaqController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.activity_logs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/activity-logs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ActivityLogController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\ActivityLogController@index',
        'as' => 'admin.activity_logs.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.activity_logs.clear' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/activity-logs/clear',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ActivityLogController@clear',
        'controller' => 'App\\Http\\Controllers\\Admin\\ActivityLogController@clear',
        'as' => 'admin.activity_logs.clear',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.events.feed' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/events/feed',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:events_access',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\EventController@feed',
        'controller' => 'App\\Http\\Controllers\\Admin\\EventController@feed',
        'as' => 'admin.events.feed',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.events.calendar.settings' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/events/calendar/settings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:events_edit',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\EventController@updateCalendarSettings',
        'controller' => 'App\\Http\\Controllers\\Admin\\EventController@updateCalendarSettings',
        'as' => 'admin.events.calendar.settings',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.events.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/events',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:events_access',
        ),
        'as' => 'admin.events.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\EventController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\EventController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.events.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/events/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:events_access',
        ),
        'as' => 'admin.events.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\EventController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\EventController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.events.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/events',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:events_access',
        ),
        'as' => 'admin.events.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\EventController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\EventController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.events.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/events/{event}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:events_access',
        ),
        'as' => 'admin.events.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\EventController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\EventController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.events.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/events/{event}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:events_access',
        ),
        'as' => 'admin.events.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\EventController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\EventController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.events.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/events/{event}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:events_access',
        ),
        'as' => 'admin.events.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\EventController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\EventController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.events.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/events/{event}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:events_access',
        ),
        'as' => 'admin.events.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\EventController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\EventController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.points-rules.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/points-rules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:points_access',
        ),
        'as' => 'admin.points-rules.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\PointsRuleController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\PointsRuleController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.points-rules.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/points-rules/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:points_access',
        ),
        'as' => 'admin.points-rules.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\PointsRuleController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\PointsRuleController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.points-rules.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/points-rules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:points_access',
        ),
        'as' => 'admin.points-rules.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\PointsRuleController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\PointsRuleController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.points-rules.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/points-rules/{points_rule}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:points_access',
        ),
        'as' => 'admin.points-rules.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\PointsRuleController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\PointsRuleController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.points-rules.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/points-rules/{points_rule}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:points_access',
        ),
        'as' => 'admin.points-rules.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\PointsRuleController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\PointsRuleController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.points-rules.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/points-rules/{points_rule}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:points_access',
        ),
        'as' => 'admin.points-rules.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\PointsRuleController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\PointsRuleController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.points-rules.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/points-rules/{points_rule}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:points_access',
        ),
        'as' => 'admin.points-rules.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\PointsRuleController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\PointsRuleController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.mentorships.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/mentorships',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:mentorships_access',
        ),
        'as' => 'admin.mentorships.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\MentorshipController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\MentorshipController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.mentorships.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/mentorships/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:mentorships_access',
        ),
        'as' => 'admin.mentorships.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\MentorshipController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\MentorshipController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.mentorships.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/mentorships',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:mentorships_access',
        ),
        'as' => 'admin.mentorships.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\MentorshipController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\MentorshipController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.mentorships.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/mentorships/{mentorship}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:mentorships_access',
        ),
        'as' => 'admin.mentorships.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\MentorshipController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\MentorshipController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.mentorships.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/mentorships/{mentorship}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:mentorships_access',
        ),
        'as' => 'admin.mentorships.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\MentorshipController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\MentorshipController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.mentorships.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/mentorships/{mentorship}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:mentorships_access',
        ),
        'as' => 'admin.mentorships.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\MentorshipController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\MentorshipController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.mentorships.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/mentorships/{mentorship}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:mentorships_access',
        ),
        'as' => 'admin.mentorships.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\MentorshipController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\MentorshipController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.ranking' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/ranking',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:ranking_access',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\RankingController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\RankingController@index',
        'as' => 'admin.ranking',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/jobs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.jobs.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\JobController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/jobs/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.jobs.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\JobController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/jobs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.jobs.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\JobController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/jobs/{job}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.jobs.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\JobController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/jobs/{job}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.jobs.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\JobController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/jobs/{job}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.jobs.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\JobController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/jobs/{job}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.jobs.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\JobController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.redemptions.approve' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/redemptions/{redemption}/approve',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\RedemptionController@approve',
        'controller' => 'App\\Http\\Controllers\\Admin\\RedemptionController@approve',
        'as' => 'admin.redemptions.approve',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.redemptions.cancel' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/redemptions/{redemption}/cancel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\RedemptionController@cancel',
        'controller' => 'App\\Http\\Controllers\\Admin\\RedemptionController@cancel',
        'as' => 'admin.redemptions.cancel',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.redemptions.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/redemptions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.redemptions.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\RedemptionController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\RedemptionController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.redemptions.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/redemptions/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.redemptions.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\RedemptionController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\RedemptionController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.redemptions.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/redemptions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.redemptions.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\RedemptionController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\RedemptionController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.redemptions.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/redemptions/{redemption}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.redemptions.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\RedemptionController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\RedemptionController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.redemptions.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/redemptions/{redemption}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.redemptions.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\RedemptionController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\RedemptionController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.redemptions.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/redemptions/{redemption}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.redemptions.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\RedemptionController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\RedemptionController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.redemptions.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/redemptions/{redemption}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'as' => 'admin.redemptions.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\RedemptionController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\RedemptionController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.upload.test' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/upload/test',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:uploads_access',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:12:"fn() => null";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000009800000000000000000";}}',
        'as' => 'admin.upload.test',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.mailtest' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/mailtest',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:mailtest_access',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:12:"fn() => null";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000009810000000000000000";}}',
        'as' => 'admin.mailtest',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.upload.chunk' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/upload/chunk',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:uploads_chunk',
        ),
        'uses' => 'App\\Http\\Controllers\\UploadChunkController@storeChunk',
        'controller' => 'App\\Http\\Controllers\\UploadChunkController@storeChunk',
        'as' => 'admin.upload.chunk',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.upload.assemble' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/upload/assemble',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:uploads_assemble',
        ),
        'uses' => 'App\\Http\\Controllers\\UploadChunkController@assemble',
        'controller' => 'App\\Http\\Controllers\\UploadChunkController@assemble',
        'as' => 'admin.upload.assemble',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.mailtemplates.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/mailtemplates',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:mailtemplates_access',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@index',
        'as' => 'admin.mailtemplates.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.mailtemplates.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/mailtemplates/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:mailtemplates_create',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@create',
        'as' => 'admin.mailtemplates.create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.mailtemplates.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/mailtemplates',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:mailtemplates_store',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@store',
        'as' => 'admin.mailtemplates.store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.mailtemplates.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/mailtemplates/{mailtemplate}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:mailtemplates_edit',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@edit',
        'as' => 'admin.mailtemplates.edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.mailtemplates.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/mailtemplates/{mailtemplate}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:mailtemplates_update',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@update',
        'as' => 'admin.mailtemplates.update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.mailtemplates.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/mailtemplates/{mailtemplate}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:mailtemplates_delete',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@destroy',
        'as' => 'admin.mailtemplates.destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.mailtemplates.preview' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/mailtemplates/{mailtemplate}/preview',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:mailtemplates_preview',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@preview',
        'controller' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@preview',
        'as' => 'admin.mailtemplates.preview',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.mailtemplates.sendpreview' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/mailtemplates/{mailtemplate}/send-preview',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:mailtemplates_sendpreview',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@sendPreview',
        'controller' => 'App\\Http\\Controllers\\Admin\\MailTemplateController@sendPreview',
        'as' => 'admin.mailtemplates.sendpreview',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.orders.refund' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/orders/{order}/refund',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:orders_refund',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\OrderController@refund',
        'controller' => 'App\\Http\\Controllers\\Admin\\OrderController@refund',
        'as' => 'admin.orders.refund',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.orders.approve' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/orders/{order}/approve',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\OrderController@approveManually',
        'controller' => 'App\\Http\\Controllers\\Admin\\OrderController@approveManually',
        'as' => 'admin.orders.approve',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.orders.cancel' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/orders/{order}/cancel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\OrderController@cancel',
        'controller' => 'App\\Http\\Controllers\\Admin\\OrderController@cancel',
        'as' => 'admin.orders.cancel',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.orders.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/orders',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:orders_access',
        ),
        'as' => 'admin.orders.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\OrderController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\OrderController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.orders.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/orders/{order}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:orders_access',
        ),
        'as' => 'admin.orders.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\OrderController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\OrderController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.orders.invoice' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/orders/{order}/invoice',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:invoices_issue',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\InvoiceController@issueForOrder',
        'controller' => 'App\\Http\\Controllers\\Admin\\InvoiceController@issueForOrder',
        'as' => 'admin.orders.invoice',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.invoices.send' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/invoices/{invoice}/send',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:invoices_send',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\InvoiceController@send',
        'controller' => 'App\\Http\\Controllers\\Admin\\InvoiceController@send',
        'as' => 'admin.invoices.send',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.invoices.pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/invoices/{invoice}/pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:invoices_pdf',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\InvoiceController@pdf',
        'controller' => 'App\\Http\\Controllers\\Admin\\InvoiceController@pdf',
        'as' => 'admin.invoices.pdf',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.invoices.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/invoices',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:invoices_access',
        ),
        'as' => 'admin.invoices.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\InvoiceController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\InvoiceController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.invoices.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/invoices/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:invoices_access',
        ),
        'as' => 'admin.invoices.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\InvoiceController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\InvoiceController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.invoices.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/invoices',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:invoices_access',
        ),
        'as' => 'admin.invoices.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\InvoiceController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\InvoiceController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.invoices.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/invoices/{invoice}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:invoices_access',
        ),
        'as' => 'admin.invoices.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\InvoiceController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\InvoiceController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.invoices.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/invoices/{invoice}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:invoices_access',
        ),
        'as' => 'admin.invoices.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\InvoiceController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\InvoiceController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.invoices.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/invoices/{invoice}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:invoices_access',
        ),
        'as' => 'admin.invoices.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\InvoiceController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\InvoiceController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.invoices.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/invoices/{invoice}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:invoices_access',
        ),
        'as' => 'admin.invoices.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\InvoiceController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\InvoiceController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.social.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/social',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:social_access',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SocialController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\SocialController@index',
        'as' => 'admin.social.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.social.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/social/{post}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'App\\Http\\Middleware\\AdminMiddleware',
          3 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          4 => 'check.plan',
          5 => 'App\\Http\\Middleware\\EnsureUserIsAdmin',
          6 => 'check.feature:social_delete',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SocialController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\SocialController@destroy',
        'as' => 'admin.social.destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
  ),
)
);

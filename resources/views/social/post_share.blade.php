<?php
/**
 * =============================================================================
 * AVISO LEGAL DE DIREITOS AUTORAIS E PROPRIEDADE INTELECTUAL
 * =============================================================================
 *
 * © 2026 Marcelo Brad - Todos os direitos reservados.
 *
 * AUTOR:
 * marcelo-brad rj
 *
 * CONTATO:
 * Tel: +55 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 * WhatsApp: +55 21 98132-5441
 *
 * -----------------------------------------------------------------------------
 * DIREITOS AUTORAIS:
 * Este software, incluindo seu código-fonte, estrutura, banco de dados,
 * layout, funcionalidades, lógica de programação e documentação associada,
 * é protegido pelas leis brasileiras de direitos autorais (Lei nº 9.610/98)
 * e demais legislações internacionais aplicáveis.
 *
 * -----------------------------------------------------------------------------
 * PROPRIEDADE INTELECTUAL:
 * Todo o conteúdo deste sistema é de propriedade exclusiva do autor,
 * sendo proibida a reprodução total ou parcial, modificação,
 * engenharia reversa, redistribuição, sublicenciamento,
 * comercialização ou qualquer forma de exploração sem autorização
 * expressa e formal do titular dos direitos.
 *
 * -----------------------------------------------------------------------------
 * LICENÇA DE USO:
 * Este sistema é licenciado, não vendido.
 * O uso é restrito ao cliente contratante conforme contrato firmado.
 * É vedado o compartilhamento, revenda ou distribuição a terceiros
 * sem autorização prévia e documentada.
 *
 * -----------------------------------------------------------------------------
 * RESPONSABILIDADE:
 * Alterações realizadas por terceiros não autorizados anulam qualquer
 * responsabilidade do autor sobre falhas, vulnerabilidades ou danos
 * decorrentes do uso indevido do sistema.
 *
 * -----------------------------------------------------------------------------
 * SEGURANÇA E MONITORAMENTO:
 * Este software pode conter mecanismos de identificação,
 * rastreamento de licença e validação de integridade para
 * proteção contra uso não autorizado e pirataria.
 *
 * -----------------------------------------------------------------------------
 * PENALIDADES:
 * O uso indevido ou não autorizado poderá resultar em medidas legais
 * cabíveis nas esferas civil e criminal, incluindo indenizações por
 * perdas e danos.
 *
 * =============================================================================
 */
?>

@extends('layouts.app')

@section('title', 'Post de ' . $post->user->name)
@section('meta_title', 'Post de ' . $post->user->name)
@section('meta_description', $shareDescription)
@section('meta_image', $shareImage)
@section('twitter_image', $shareImage)
@section('og_type', 'article')

@section('content')
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-3xl mx-auto px-4">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="rounded-full w-12 h-12 overflow-hidden flex-shrink-0">
                        <img src="{{ $post->user->profile_photo_url }}" alt="Avatar" class="w-12 h-12 object-cover"
                            onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-900">{{ $post->user->name }}</h2>
                        <p class="text-xs text-gray-500">{{ $post->created_at->diffForHumans() }}</p>
                    </div>
                </div>

                <div class="prose max-w-none text-gray-800">
                    {!! nl2br(e($post->content)) !!}
                </div>

                @if($post->media->isNotEmpty())
                    <div class="mt-4">
                        <img src="{{ asset($post->media->first()->path) }}" alt="Midia do post"
                            class="w-full rounded-lg object-cover">
                    </div>
                @endif

                <div class="mt-6">
                    <a href="{{ route('social.feed') }}" class="text-blue-600 hover:underline">
                        Ver mais na comunidade
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

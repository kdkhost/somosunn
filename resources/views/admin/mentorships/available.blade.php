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

@extends('admin.layouts.app')

@section('page_title', 'Mentorias Disponíveis')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-success card-outline">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h2 class="h5 font-weight-bold mb-1">Pronto para o próximo nível?</h2>
                        <p class="text-muted mb-0">Escolha uma mentoria e acelere seu crescimento com orientação prática de especialistas.</p>
                    </div>
                    <div class="d-none d-md-block text-success">
                        <i class="fas fa-users-cog fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" class="mb-4">
        <div class="input-group input-group-lg">
            <input type="text" name="q" class="form-control" placeholder="O que você deseja aprender hoje?"
                value="{{ $search ?? '' }}">
            <div class="input-group-append">
                <button class="btn btn-success" type="submit"><i class="fas fa-search"></i></button>
            </div>
        </div>
    </form>

    <div class="row">
        @forelse($items as $item)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="badge badge-success">
                                R$ {{ number_format((float) $item->price, 2, ',', '.') }}
                            </div>
                            <span class="text-xs text-muted font-weight-bold text-uppercase"
                                style="font-size: 0.75rem;">{{ $item->mentor?->name ?? 'Mentor UNN' }}</span>
                        </div>

                        <h3 class="h5 font-weight-bold mb-3 text-dark" style="min-height: 3rem;">{{ $item->title }}</h3>
                        <p class="text-muted small mb-4" style="min-height: 4.5rem;">
                            {{ \Illuminate\Support\Str::limit(strip_tags((string) $item->description), 140) }}
                        </p>

                        <div class="row mb-4">
                            <div class="col-6">
                                <div class="bg-light rounded p-2 text-center">
                                    <small class="d-block text-muted font-weight-bold text-uppercase"
                                        style="font-size: 0.65rem;">Vagas</small>
                                    <span class="font-weight-bold">{{ $item->slots ?: 'Ilimitadas' }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded p-2 text-center">
                                    <small class="d-block text-muted font-weight-bold text-uppercase"
                                        style="font-size: 0.65rem;">Formato</small>
                                    <span class="font-weight-bold">Premium</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="{{ route('mentorships.show', $item->id) }}"
                            class="btn btn-primary btn-block">
                            <i class="fas fa-external-link-alt mr-1"></i> Ver Detalhes
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="callout callout-info">
                    <h5 class="mb-1">Nenhuma mentoria encontrada.</h5>
                    <p class="mb-0">Tente buscar por termos diferentes ou confira novamente em breve.</p>
                </div>
            </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $items->links() }}
    </div>

@endsection
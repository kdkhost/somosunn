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
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Mentorias Disponiveis</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">Escolha uma mentoria e acelere seu crescimento com orientacao pratica de especialistas.</p>

            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="O que voce deseja aprender hoje?"
                        value="{{ $search ?? '' }}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </form>

            <div class="row">
                @forelse($items as $item)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge badge-success">
                                        R$ {{ number_format((float) $item->price, 2, ',', '.') }}
                                    </span>
                                    <span class="text-xs text-muted text-uppercase">{{ $item->mentor?->name ?? 'Mentor UNN' }}</span>
                                </div>

                                <h3 class="h6 font-weight-bold mb-2 text-dark">{{ $item->title }}</h3>
                                <p class="text-muted small">
                                    {{ \Illuminate\Support\Str::limit(strip_tags((string) $item->description), 140) }}
                                </p>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="bg-light rounded p-2 text-center">
                                            <small class="d-block text-muted text-uppercase">Vagas</small>
                                            <span class="font-weight-bold">{{ $item->slots ?: 'Ilimitadas' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-light rounded p-2 text-center">
                                            <small class="d-block text-muted text-uppercase">Formato</small>
                                            <span class="font-weight-bold">Premium</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="{{ route('mentorships.show', $item->id) }}" class="btn btn-primary btn-block">
                                    <i class="fas fa-external-link-alt mr-1"></i> Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-4 text-muted">Nenhuma mentoria encontrada.</div>
                @endforelse
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $items->links() }}
            </div>
        </div>
    </div>

@endsection
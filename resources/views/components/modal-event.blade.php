{{-- Componente Modal de Evento com imagem de fundo --}}
<div class="modal fade" id="modalEvent" tabindex="-1" role="dialog" aria-labelledby="modalEventLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content p-0 border-0" style="overflow:hidden;">
      <div class="modal-event-bg position-relative" style="background: url('{{ $image_url }}') center/cover no-repeat; min-height: 420px;">
        <div class="modal-event-overlay position-absolute w-100 h-100" style="background:rgba(0,0,0,0.45);top:0;left:0;"></div>
        <button type="button" class="close position-absolute" style="top:18px;right:18px;z-index:2;" data-dismiss="modal" aria-label="Fechar">
          <span aria-hidden="true" style="font-size:2rem;color:#fff">&times;</span>
        </button>
        <div class="modal-event-content position-relative d-flex flex-column align-items-center justify-content-center text-center w-100 h-100" style="z-index:2;min-height:420px;">
          <div class="mb-4">
            <i class="fas fa-info-circle" style="font-size:3rem;color:#38bdf8;"></i>
          </div>
          <h2 class="font-weight-bold text-white mb-3" style="font-size:2rem;">Novo evento na comunidade</h2>
          <div class="bg-white rounded shadow-sm px-4 py-3 mb-4 d-inline-block" style="max-width:340px;">
            <h3 class="font-weight-bold text-primary mb-1">{{ $title }}</h3>
            <div class="text-muted mb-2">Data: {{ $date }}</div>
          </div>
          <a href="{{ $action_url }}" class="btn btn-primary btn-lg px-5 shadow-sm" style="font-size:1.15rem;">APROVEITAR</a>
        </div>
      </div>
    </div>
  </div>
</div>
<style>
.modal-event-bg { min-height:420px; }
.modal-event-content { min-height:420px; }
@media (max-width: 600px) {
  .modal-event-bg, .modal-event-content { min-height:320px; }
}
</style>

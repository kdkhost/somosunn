@php
    $company = $company ?? [];
    $number = $invoice->number ?: ('#' . $invoice->id);
    $amount = 'R$ ' . number_format((float) $invoice->total_amount, 2, ',', '.');
    $issuedAt = $invoice->issued_at ? $invoice->issued_at->format('d/m/Y') : now()->format('d/m/Y');
@endphp

<div style="background:#f4f6f9;padding:24px;font-family:Arial,Helvetica,sans-serif;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,0.08);">
        <div style="padding:18px 22px;background:linear-gradient(135deg,#1F5EDB 0%, #1D3FC4 100%);color:#fff;">
            <div style="font-size:18px;font-weight:700;">{{ $company['name'] ?? 'UNN' }}</div>
            <div style="font-size:12px;opacity:.9;">Fatura {{ $number }}</div>
        </div>

        <div style="padding:22px;color:#111827;">
            <p style="margin:0 0 10px;font-size:15px;">Olá, <strong>{{ $invoice->user?->name ?? 'cliente' }}</strong>!</p>

            <p style="margin:0 0 14px;font-size:14px;color:#374151;line-height:1.6;">
                Segue em anexo a sua <strong>fatura em PDF</strong>.
            </p>

            <div style="border:1px solid #e5e7eb;border-radius:10px;padding:14px;background:#f9fafb;">
                <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <div>
                        <div style="font-size:12px;color:#6b7280;">Número</div>
                        <div style="font-size:14px;font-weight:700;">{{ $number }}</div>
                    </div>
                    <div>
                        <div style="font-size:12px;color:#6b7280;">Emissão</div>
                        <div style="font-size:14px;font-weight:700;">{{ $issuedAt }}</div>
                    </div>
                    <div>
                        <div style="font-size:12px;color:#6b7280;">Valor</div>
                        <div style="font-size:14px;font-weight:700;">{{ $amount }}</div>
                    </div>
                </div>
            </div>

            <p style="margin:14px 0 0;font-size:12px;color:#6b7280;line-height:1.6;">
                Se tiver dúvidas, responda este e-mail ou entre em contato: <strong>{{ $company['email'] ?? '' }}</strong>
            </p>
        </div>

        <div style="padding:14px 22px;background:#f9fafb;border-top:1px solid #e5e7eb;color:#6b7280;font-size:12px;">
            © {{ date('Y') }} {{ $company['name'] ?? 'UNN' }} · {{ $company['site'] ?? '' }}
        </div>
    </div>
</div>


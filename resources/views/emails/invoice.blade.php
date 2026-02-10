@php
    $company = $company ?? [];
    $number = $invoice->number ?: ('#' . $invoice->id);
    $amount = 'R$ ' . number_format((float) $invoice->total_amount, 2, ',', '.');
    $issuedAt = $invoice->issued_at ? $invoice->issued_at->format('d/m/Y') : now()->format('d/m/Y');
    $primaryColor = $company['primary_color'] ?? '#1F5EDB';
    $logoUrl = $company['logo_url'] ?? null;
@endphp

<div
    style="background-color: #f3f4f6; padding: 40px 20px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <div
        style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);">

        <!-- Header -->
        <div style="background-color: #ffffff; padding: 32px; border-bottom: 1px solid #f3f4f6; text-align: center;">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $company['name'] }}" style="max-height: 50px; margin-bottom: 20px;">
            @else
                <div style="font-size: 24px; font-weight: 800; color: {{ $primaryColor }}; margin-bottom: 10px;">
                    {{ $company['name'] }}</div>
            @endif
            <div
                style="text-transform: uppercase; letter-spacing: 2px; font-size: 12px; font-weight: 700; color: #6b7280;">
                Fatura Confirmada</div>
        </div>

        <!-- Body -->
        <div style="padding: 40px 32px; color: #1f2937;">
            <p style="font-size: 18px; font-weight: 600; margin-bottom: 16px; color: #111827;">Olá,
                {{ explode(' ', $invoice->user->name ?? 'Cliente')[0] }}!</p>
            <p style="font-size: 16px; line-height: 1.6; color: #4b5563; margin-bottom: 32px;">
                Recebemos o registro da fatura <strong>{{ $number }}</strong>. Você pode encontrar os detalhes do seu
                pedido em anexo neste e-mail.
            </p>

            <!-- Summary Box -->
            <div
                style="background-color: #f9fafb; border-radius: 12px; padding: 24px; border: 1px solid #f3f4f6; margin-bottom: 32px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding-bottom: 16px;">
                            <div
                                style="font-size: 12px; color: #9ca3af; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">
                                Número</div>
                            <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ $number }}</div>
                        </td>
                        <td style="padding-bottom: 16px; text-align: right;">
                            <div
                                style="font-size: 12px; color: #9ca3af; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">
                                Valor Total</div>
                            <div style="font-size: 16px; font-weight: 600; color: {{ $primaryColor }};">{{ $amount }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div
                                style="font-size: 12px; color: #9ca3af; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">
                                Data</div>
                            <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ $issuedAt }}</div>
                        </td>
                        <td style="text-align: right;">
                            <div
                                style="font-size: 12px; color: #9ca3af; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">
                                Status</div>
                            <div
                                style="display: inline-block; padding: 4px 12px; background-color: #ecfdf5; color: #065f46; border-radius: 9999px; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                                {{ $invoice->status === 'paid' ? 'Pago' : 'Emitido' }}</div>
                        </td>
                    </tr>
                </table>
            </div>

            <p style="font-size: 14px; text-align: center; color: #9ca3af; margin-bottom: 0;">
                O PDF completo da fatura está anexado a esta mensagem para seus registros.
            </p>
        </div>

        <!-- Footer -->
        <div style="padding: 32px; background-color: #f9fafb; text-align: center; border-top: 1px solid #f3f4f6;">
            <div style="font-size: 14px; color: #111827; font-weight: 600; margin-bottom: 8px;">{{ $company['name'] }}
            </div>
            <div style="font-size: 12px; color: #9ca3af;">
                {{ $company['site'] }}<br>
                Este é um e-mail automático, por favor não responda.
            </div>
        </div>
    </div>
</div>